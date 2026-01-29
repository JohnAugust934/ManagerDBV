<?php

namespace App\Http\Controllers;

use App\Models\Desbravador;
use App\Models\Unidade;
use Illuminate\Http\Request;

class DesbravadorController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->input('status', 'ativos'); // Padrão: ativos

        $query = Desbravador::with('unidade')->orderBy('nome');

        if ($status === 'ativos') {
            $query->where('ativo', true);
        } elseif ($status === 'inativos') {
            $query->where('ativo', false);
        }
        // Se for 'todos', não aplica filtro de ativo

        $desbravadores = $query->get();

        return view('desbravadores.index', compact('desbravadores', 'status'));
    }

    public function create()
    {
        $unidades = Unidade::orderBy('nome')->get();
        return view('desbravadores.create', compact('unidades'));
    }

    public function store(Request $request)
    {
        // Regras de validação estritas conforme solicitado
        $dados = $request->validate([
            // Dados do Clube
            'nome' => 'required|string|max:255',
            'data_nascimento' => 'required|date',
            'sexo' => 'required|in:M,F',
            'unidade_id' => 'required|exists:unidades,id',
            'classe_atual' => 'required|string',

            // Dados Pessoais e Contato (Agora Obrigatórios)
            'email' => 'required|email',
            'telefone' => 'nullable|string', // Telefone próprio pode ser opcional se tiver o do pai
            'endereco' => 'required|string|max:500',
            'nome_responsavel' => 'required|string|max:255',
            'telefone_responsavel' => 'required|string',
            'numero_sus' => 'required|string|max:50', // Obrigatório

            // Dados Médicos (Opcionais, mas mantidos)
            'tipo_sanguineo' => 'nullable|string|max:3',
            'alergias' => 'nullable|string',
            'medicamentos_continuos' => 'nullable|string',
            'plano_saude' => 'nullable|string',
        ]);

        $dados['ativo'] = true; // Novo cadastro nasce ativo

        Desbravador::create($dados);

        return redirect()->route('desbravadores.index')->with('success', 'Desbravador cadastrado com sucesso!');
    }

    public function show(Desbravador $desbravador)
    {
        $desbravador->load(['unidade', 'especialidades', 'frequencias' => function ($q) {
            $q->orderBy('data', 'desc')->take(5);
        }]);

        return view('desbravadores.show', compact('desbravador'));
    }

    public function edit(Desbravador $desbravador)
    {
        $unidades = Unidade::orderBy('nome')->get();
        return view('desbravadores.edit', compact('desbravador', 'unidades'));
    }

    public function update(Request $request, Desbravador $desbravador)
    {
        $dados = $request->validate([
            'nome' => 'required|string|max:255',
            'ativo' => 'boolean',
            'data_nascimento' => 'required|date',
            'sexo' => 'required|in:M,F',
            'unidade_id' => 'required|exists:unidades,id',
            'classe_atual' => 'required|string',
            'email' => 'required|email',
            'telefone' => 'nullable|string',
            'endereco' => 'required|string',
            'nome_responsavel' => 'required|string',
            'telefone_responsavel' => 'required|string',
            'numero_sus' => 'required|string',
            'tipo_sanguineo' => 'nullable|string|max:3',
            'alergias' => 'nullable|string',
            'medicamentos_continuos' => 'nullable|string',
            'plano_saude' => 'nullable|string',
        ]);

        // Checkbox não marcado não envia valor no request
        $dados['ativo'] = $request->has('ativo');

        $desbravador->update($dados);

        return redirect()->route('desbravadores.show', $desbravador)->with('success', 'Dados atualizados!');
    }

    /**
     * Tela de gestão de especialidades do desbravador.
     */
    public function gerenciarEspecialidades(Desbravador $desbravador)
    {
        $especialidades = \App\Models\Especialidade::orderBy('nome')->get();
        return view('desbravadores.especialidades', compact('desbravador', 'especialidades'));
    }

    /**
     * Salva as especialidades selecionadas.
     */
    public function salvarEspecialidades(Request $request, Desbravador $desbravador)
    {
        $dados = $request->validate([
            'especialidades' => 'array',
            'especialidades.*' => 'exists:especialidades,id',
            'data_conclusao' => 'required|date',
        ]);

        if ($request->has('especialidades')) {
            // Prepara array para sync com dado extra na pivot
            $syncData = [];
            foreach ($request->especialidades as $espId) {
                $syncData[$espId] = ['data_conclusao' => $request->data_conclusao];
            }

            // Sync sem apagar as antigas (para adicionar novas)
            $desbravador->especialidades()->syncWithoutDetaching($syncData);

            return back()->with('success', 'Especialidades adicionadas com sucesso!');
        }

        return back()->with('warning', 'Nenhuma especialidade selecionada.');
    }

    /**
     * Remove uma especialidade específica.
     */
    public function removerEspecialidade(Desbravador $desbravador, $especialidadeId)
    {
        $desbravador->especialidades()->detach($especialidadeId);
        return back()->with('success', 'Especialidade removida.');
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Desbravador;
use App\Models\Especialidade;
use App\Models\Unidade; // Importante para o método de especialidades
use Illuminate\Http\Request;

class DesbravadorController extends Controller
{
    public function index(Request $request)
    {
        // Inicia a query carregando a unidade para otimizar (Eager Loading)
        $query = Desbravador::with('unidade')->orderBy('nome');

        // 1. Filtro da Barra de Busca (Nome ou Email)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nome', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // 2. Filtro por Unidade (Dropdown)
        if ($request->filled('unidade_id')) {
            $query->where('unidade_id', $request->unidade_id);
        }

        // 3. Filtro de Status (Padrão: Apenas ativos, a menos que solicitado)
        $status = $request->input('status', 'ativos');
        if ($status === 'ativos') {
            $query->where('ativo', true);
        } elseif ($status === 'inativos') {
            $query->where('ativo', false);
        }

        // CORREÇÃO PRINCIPAL: Usar paginate(10) em vez de get()
        // Isso resolve o erro "Method appends does not exist"
        $desbravadores = $query->paginate(10);

        return view('desbravadores.index', compact('desbravadores', 'status'));
    }

    public function create()
    {
        $unidades = Unidade::orderBy('nome')->get();

        return view('desbravadores.create', compact('unidades'));
    }

    public function store(Request $request)
    {
        $dados = $request->validate([
            // Dados do Clube
            'nome' => 'required|string|max:255',
            'data_nascimento' => 'required|date',
            'sexo' => 'required|in:M,F',
            'unidade_id' => 'required|exists:unidades,id',
            'classe_atual' => 'required|string',

            // Dados Pessoais e Contato
            'email' => 'required|email',
            'telefone' => 'nullable|string',
            'endereco' => 'required|string|max:500',
            'nome_responsavel' => 'required|string|max:255',
            'telefone_responsavel' => 'required|string',
            'numero_sus' => 'required|string|max:50',

            // Dados Médicos
            'tipo_sanguineo' => 'nullable|string|max:3',
            'alergias' => 'nullable|string',
            'medicamentos_continuos' => 'nullable|string',
            'plano_saude' => 'nullable|string',
        ]);

        $dados['ativo'] = true;

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

        $dados['ativo'] = $request->has('ativo');

        $desbravador->update($dados);

        return redirect()->route('desbravadores.show', $desbravador)->with('success', 'Dados atualizados!');
    }

    public function gerenciarEspecialidades(Desbravador $desbravador)
    {
        $especialidades = Especialidade::orderBy('nome')->get();

        return view('desbravadores.especialidades', compact('desbravador', 'especialidades'));
    }

    public function salvarEspecialidades(Request $request, Desbravador $desbravador)
    {
        $request->validate([
            'especialidades' => 'array',
            'especialidades.*' => 'exists:especialidades,id',
            'data_conclusao' => 'required|date',
        ]);

        if ($request->has('especialidades')) {
            $syncData = [];
            foreach ($request->especialidades as $espId) {
                $syncData[$espId] = ['data_conclusao' => $request->data_conclusao];
            }
            $desbravador->especialidades()->syncWithoutDetaching($syncData);

            return back()->with('success', 'Especialidades adicionadas com sucesso!');
        }

        return back()->with('warning', 'Nenhuma especialidade selecionada.');
    }

    public function removerEspecialidade(Desbravador $desbravador, $especialidadeId)
    {
        $desbravador->especialidades()->detach($especialidadeId);

        return back()->with('success', 'Especialidade removida.');
    }
}

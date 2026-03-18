<?php

namespace App\Http\Controllers;

use App\Models\Classe;
use App\Models\Desbravador;
use App\Models\Especialidade;
use App\Models\Unidade;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DesbravadorController extends Controller
{
    public function index(Request $request)
    {
        $query = Desbravador::with(['unidade', 'classe'])->orderBy('nome');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nome', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('cpf', 'like', "%{$search}%");
            });
        }

        if ($request->filled('unidade_id')) {
            $query->where('unidade_id', $request->unidade_id);
        }

        $status = $request->input('status', 'ativos');
        if ($status === 'ativos') {
            $query->where('ativo', true);
        } elseif ($status === 'inativos') {
            $query->where('ativo', false);
        }

        $desbravadores = $query->paginate(10);

        return view('desbravadores.index', compact('desbravadores', 'status'));
    }

    public function create()
    {
        $unidades = Unidade::orderBy('nome')->get();
        $classes = Classe::orderBy('ordem')->get();

        return view('desbravadores.create', compact('unidades', 'classes'));
    }

    public function store(Request $request)
    {
        $dados = $request->validate([
            'nome' => 'required|string|max:255',
            'data_nascimento' => 'required|date',
            'sexo' => 'required|in:M,F',
            'cpf' => 'required|string|max:14|unique:desbravadores,cpf',
            'rg' => 'nullable|string|max:20',
            'unidade_id' => 'required|exists:unidades,id',
            'classe_atual' => 'nullable|exists:classes,id',
            'email' => 'required|email',
            'telefone' => 'nullable|string',
            'endereco' => 'required|string|max:500',
            'nome_responsavel' => 'required|string|max:255',
            'telefone_responsavel' => 'required|string',
            'numero_sus' => 'required|string|max:50',
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
        $desbravador->load(['unidade', 'classe', 'especialidades', 'frequencias' => function ($q) {
            $q->orderBy('data', 'desc')->take(5);
        }]);

        return view('desbravadores.show', compact('desbravador'));
    }

    public function edit(Desbravador $desbravador)
    {
        $unidades = Unidade::orderBy('nome')->get();
        $classes = Classe::orderBy('ordem')->get();

        return view('desbravadores.edit', compact('desbravador', 'unidades', 'classes'));
    }

    public function update(Request $request, Desbravador $desbravador)
    {
        $dados = $request->validate([
            'nome' => 'required|string|max:255',
            'ativo' => 'boolean',
            'data_nascimento' => 'required|date',
            'sexo' => 'required|in:M,F',
            'cpf' => 'required|string|max:14|unique:desbravadores,cpf,'.$desbravador->id,
            'rg' => 'nullable|string|max:20',
            'unidade_id' => 'required|exists:unidades,id',
            'classe_atual' => 'nullable|exists:classes,id',
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

    public function destroy(Desbravador $desbravador)
    {
        DB::transaction(function () use ($desbravador) {
            $desbravador->delete();
        });

        return redirect()
            ->route('desbravadores.index')
            ->with('success', 'Desbravador excluído com sucesso. Todos os dados vinculados foram removidos.');
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

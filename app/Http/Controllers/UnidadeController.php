<?php

namespace App\Http\Controllers;

use App\Models\Unidade;
use Illuminate\Http\Request;

class UnidadeController extends Controller
{
    public function index()
    {
        $unidades = Unidade::withCount('desbravadores')->orderBy('nome')->get();
        return view('unidades.index', compact('unidades'));
    }

    public function create()
    {
        return view('unidades.create');
    }

    public function store(Request $request)
    {
        $dados = $request->validate([
            'nome' => 'required|string|max:255|unique:unidades,nome',
            'conselheiro' => 'required|string|max:255', // Agora obrigatório
            'grito_guerra' => 'nullable|string', // Opcional
        ]);

        Unidade::create($dados);

        return redirect()->route('unidades.index')->with('success', 'Unidade criada com sucesso!');
    }

    public function show(Unidade $unidade)
    {
        $unidade->load(['desbravadores' => function ($query) {
            $query->orderBy('nome')->where('ativo', true);
        }]);

        return view('unidades.show', compact('unidade'));
    }

    /**
     * Exibe formulário de edição.
     */
    public function edit(Unidade $unidade)
    {
        return view('unidades.edit', compact('unidade'));
    }

    /**
     * Salva as alterações.
     */
    public function update(Request $request, Unidade $unidade)
    {
        $dados = $request->validate([
            'nome' => 'required|string|max:255|unique:unidades,nome,' . $unidade->id,
            'conselheiro' => 'required|string|max:255',
            'grito_guerra' => 'nullable|string',
        ]);

        $unidade->update($dados);

        return redirect()->route('unidades.show', $unidade)->with('success', 'Dados da unidade atualizados!');
    }
}

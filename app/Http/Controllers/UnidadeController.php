<?php

namespace App\Http\Controllers;

use App\Models\Unidade;
use Illuminate\Http\Request;

class UnidadeController extends Controller
{
    // 1. Mostrar a lista de unidades
    public function index()
    {
        $unidades = Unidade::all(); // Pega tudo do banco
        return view('unidades.index', compact('unidades'));
    }

    // 2. Mostrar o formulário de cadastro
    public function create()
    {
        return view('unidades.create');
    }

    // 3. Receber os dados do formulário e salvar
    public function store(Request $request)
    {
        // Validação (O nome é obrigatório)
        $request->validate([
            'nome' => 'required|string|max:255',
            'conselheiro' => 'nullable|string|max:255',
        ]);

        // Salvar no Banco
        Unidade::create($request->all());

        // Voltar para a lista com mensagem de sucesso
        return redirect()->route('unidades.index')
            ->with('success', 'Unidade criada com sucesso!');
    }
}

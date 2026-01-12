<?php

namespace App\Http\Controllers;

use App\Models\Ato;
use App\Models\Desbravador;
use Illuminate\Http\Request;

class AtoController extends Controller
{
    public function index()
    {
        // Traz os atos com o nome do desbravador (se houver)
        $atos = Ato::with('desbravador')->orderBy('data', 'desc')->get();
        return view('secretaria.atos.index', compact('atos'));
    }

    public function create()
    {
        $desbravadores = Desbravador::where('ativo', true)->orderBy('nome')->get();
        return view('secretaria.atos.create', compact('desbravadores'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'data' => 'required|date',
            'tipo' => 'required|string',
            'descricao_resumida' => 'required|string|max:255',
            'desbravador_id' => 'nullable|exists:desbravadores,id',
        ]);

        Ato::create($request->all());

        return redirect()->route('atos.index')
            ->with('success', 'Ato administrativo registrado!');
    }
}

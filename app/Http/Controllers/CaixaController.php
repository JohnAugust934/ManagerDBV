<?php

namespace App\Http\Controllers;

use App\Models\Caixa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class CaixaController extends Controller
{
    public function index()
    {
        Gate::authorize('financeiro');

        // GlobalScope ClubScope aplica o filtro de club_id automaticamente.
        $query = Caixa::query();

        $entradas = (clone $query)->where('tipo', 'entrada')->sum('valor');
        $saidas = (clone $query)->where('tipo', 'saida')->sum('valor');
        $saldoAtual = $entradas - $saidas;

        $lancamentos = $query->orderBy('data_movimentacao', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('financeiro.caixa.index', compact('lancamentos', 'saldoAtual', 'entradas', 'saidas'));
    }

    public function create()
    {
        Gate::authorize('financeiro');

        return view('financeiro.caixa.create');
    }

    public function store(Request $request)
    {
        Gate::authorize('financeiro');

        $validado = $request->validate([
            'descricao' => 'required|string|max:255',
            'valor' => 'required|numeric|min:0.01',
            'tipo' => 'required|in:entrada,saida',
            'data_movimentacao' => 'required|date',
            'categoria' => 'nullable|string|max:100',
        ]);

        $validado['club_id'] = auth()->user()->club_id;

        Caixa::create($validado);

        return redirect()->route('caixa.index')
            ->with('success', 'Movimentação registrada com sucesso!');
    }
}

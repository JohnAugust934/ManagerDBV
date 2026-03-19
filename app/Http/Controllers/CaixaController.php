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

        // Otimização: Se houver relacionamentos futuros (ex: 'criado_por', 'categoria'),
        // adicione dentro do array with([]) para evitar queries N+1.
        $query = Caixa::query()->with([]);

        // Totais (Calculados no banco para performance)
        $entradas = (clone $query)->where('tipo', 'entrada')->sum('valor');
        $saidas = (clone $query)->where('tipo', 'saida')->sum('valor');
        $saldoAtual = $entradas - $saidas;

        // Listagem Paginada
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

        // Garante que o valor seja salvo corretamente (se vier formatado pt-BR, precisaria tratar aqui)
        Caixa::create($validado);

        return redirect()->route('caixa.index')
            ->with('success', 'Movimentação registrada com sucesso!');
    }
}

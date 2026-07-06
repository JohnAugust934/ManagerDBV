<?php

namespace App\Http\Controllers;

use App\Models\Caixa;
use App\Models\CaixaAuditLog;
use App\Services\ClubContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class CaixaController extends Controller
{
    public function index()
    {
        Gate::authorize('financeiro');

        $query = Caixa::query();

        $entradas = (clone $query)->where('tipo', 'entrada')->sum('valor');
        $saidas = (clone $query)->where('tipo', 'saida')->sum('valor');
        $saldoAtual = $entradas - $saidas;

        $lancamentos = $query->orderBy('data_movimentacao', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(15)
            ->withQueryString();

        $auditLogs = CaixaAuditLog::where('club_id', ClubContext::currentClubId())
            ->with('usuario')
            ->orderByDesc('created_at')
            ->limit(30)
            ->get();

        return view('financeiro.caixa.index', compact('lancamentos', 'saldoAtual', 'entradas', 'saidas', 'auditLogs'));
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

        $validado['club_id'] = ClubContext::currentClubId();

        // A trilha de auditoria é gravada pelo CaixaObserver (created), dentro da
        // mesma transação do retryOnDeadlock — nunca fica sem registro.
        \Illuminate\Support\Facades\DB::retryOnDeadlock(fn () => Caixa::create($validado));

        return redirect()->route('caixa.index')
            ->with('success', 'Movimentação registrada com sucesso!');
    }

    public function edit(Caixa $caixa)
    {
        Gate::authorize('financeiro');

        return view('financeiro.caixa.edit', compact('caixa'));
    }

    public function update(Request $request, Caixa $caixa)
    {
        Gate::authorize('financeiro');

        $validado = $request->validate([
            'descricao' => 'required|string|max:255',
            'valor' => 'required|numeric|min:0.01',
            'tipo' => 'required|in:entrada,saida',
            'data_movimentacao' => 'required|date',
            'categoria' => 'nullable|string|max:100',
        ]);

        // Auditoria (editado, com dados_antes/depois) gravada pelo CaixaObserver.
        \Illuminate\Support\Facades\DB::retryOnDeadlock(fn () => $caixa->update($validado));

        return redirect()->route('caixa.index')
            ->with('success', 'Lançamento atualizado com sucesso!');
    }

    public function destroy(Caixa $caixa)
    {
        Gate::authorize('financeiro');

        // Auditoria (excluido, com dados_antes) gravada pelo CaixaObserver (deleted),
        // que resolve caixa_id/club_id a partir do model ainda em memória.
        \Illuminate\Support\Facades\DB::retryOnDeadlock(fn () => $caixa->delete());

        return redirect()->route('caixa.index')
            ->with('success', 'Lançamento excluído com sucesso.');
    }
}

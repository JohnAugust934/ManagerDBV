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

        // Lançamento + trilha de auditoria numa única transação: o CaixaObserver
        // grava o log no evento `created`; se ele falhar, a movimentação é revertida
        // (nunca fica sem auditoria).
        \Illuminate\Support\Facades\DB::retryOnDeadlock(function () use ($validado) {
            Caixa::create($validado);
        });

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

        // O CaixaObserver grava o log `editado` (com dados_antes/dados_depois).
        \Illuminate\Support\Facades\DB::retryOnDeadlock(function () use ($caixa, $validado) {
            $caixa->update($validado);
        });

        return redirect()->route('caixa.index')
            ->with('success', 'Lançamento atualizado com sucesso!');
    }

    public function destroy(Caixa $caixa)
    {
        Gate::authorize('financeiro');

        // Exclusão + log na mesma transação: o CaixaObserver grava o log `excluido`
        // no evento `deleted`, com os dados anteriores do lançamento.
        \Illuminate\Support\Facades\DB::retryOnDeadlock(function () use ($caixa) {
            $caixa->delete();
        });

        return redirect()->route('caixa.index')
            ->with('success', 'Lançamento excluído com sucesso.');
    }
}

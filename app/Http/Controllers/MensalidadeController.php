<?php

namespace App\Http\Controllers;

use App\Models\Caixa;
use App\Models\Desbravador;
use App\Models\Mensalidade;
use App\Models\Unidade;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class MensalidadeController extends Controller
{
    public function index(Request $request)
    {
        Gate::authorize('financeiro');

        $clubId = auth()->user()->club_id;
        $mes = $request->input('mes', date('m'));
        $ano = $request->input('ano', date('Y'));

        $mensalidades = Mensalidade::doClube($clubId)
            ->with(['desbravador.unidade'])
            ->where('mes', $mes)
            ->where('ano', $ano)
            ->get()
            ->sortBy('desbravador.nome');

        $valorRecebido = $mensalidades->where('status', 'pago')->sum('valor');
        $valorPendente = $mensalidades->where('status', 'pendente')->sum('valor');
        $totalPago = $mensalidades->where('status', 'pago')->count();
        $totalPendente = $mensalidades->where('status', 'pendente')->count();

        $totalInadimplenteGeral = Mensalidade::doClube($clubId)->inadimplentes()->sum('valor');
        $qtdInadimplentes = Mensalidade::doClube($clubId)->inadimplentes()->count();

        $unidades = Unidade::where('club_id', $clubId)->orderBy('nome')->get();

        $inadimplenciaPorUnidade = $unidades->map(function ($unidade) {
            $query = Mensalidade::inadimplentes()
                ->whereHas('desbravador', fn ($q) => $q->where('unidade_id', $unidade->id));

            return (object) [
                'id' => $unidade->id,
                'nome' => $unidade->nome,
                'qtd' => $query->count(),
                'total' => (float) $query->sum('valor'),
            ];
        })->filter(fn ($u) => $u->qtd > 0)->values();

        return view('financeiro.mensalidades.index', compact(
            'mensalidades',
            'mes',
            'ano',
            'valorRecebido',
            'valorPendente',
            'totalPago',
            'totalPendente',
            'totalInadimplenteGeral',
            'qtdInadimplentes',
            'inadimplenciaPorUnidade'
        ));
    }

    public function gerarMassivo(Request $request)
    {
        Gate::authorize('financeiro');

        $request->validate([
            'mes' => 'required|integer|min:1|max:12',
            'ano' => 'required|integer|min:2020',
            'valor' => 'required|numeric|min:0',
        ]);

        $clubId = auth()->user()->club_id;

        // Obtém apenas IDs dos desbravadores ativos do clube — sem carregar objetos.
        $ids = Desbravador::ativos()
            ->pluck('id');

        if ($ids->isEmpty()) {
            return back()->with('warning', 'Nenhum desbravador ativo encontrado no clube.');
        }

        // Descobre quais já têm mensalidade para evitar duplicatas — 1 query.
        $existentes = Mensalidade::whereIn('desbravador_id', $ids)
            ->where('mes', $request->mes)
            ->where('ano', $request->ano)
            ->pluck('desbravador_id')
            ->flip();

        $novas = $ids
            ->reject(fn ($id) => $existentes->has($id))
            ->map(fn ($id) => [
                'desbravador_id' => $id,
                'mes' => (int) $request->mes,
                'ano' => (int) $request->ano,
                'valor' => (float) $request->valor,
                'status' => 'pendente',
                'created_at' => now(),
                'updated_at' => now(),
            ])
            ->values()
            ->all();

        if (! empty($novas)) {
            Mensalidade::insert($novas);
        }

        $count = count($novas);

        return back()->with('success', "$count mensalidades geradas com sucesso!");
    }

    public function pagar($id)
    {
        Gate::authorize('financeiro');

        $clubId = auth()->user()->club_id;

        // Garante que a mensalidade pertence ao clube do usuário.
        $mensalidade = Mensalidade::doClube($clubId)
            ->with('desbravador')
            ->findOrFail($id);

        if ($mensalidade->status === 'pago') {
            return back()->with('error', 'Esta mensalidade já consta como paga.');
        }

        DB::transaction(function () use ($mensalidade, $clubId) {
            $mensalidade->update([
                'status' => 'pago',
                'data_pagamento' => Carbon::now(),
            ]);

            Caixa::create([
                'descricao' => 'Mensalidade '.str_pad($mensalidade->mes, 2, '0', STR_PAD_LEFT).'/'.$mensalidade->ano.' - '.$mensalidade->desbravador->nome,
                'tipo' => 'entrada',
                'categoria' => 'Mensalidade',
                'valor' => $mensalidade->valor,
                'data_movimentacao' => Carbon::now(),
                'club_id' => $clubId,
            ]);
        });

        return back()->with('success', 'Pagamento recebido e lançado no caixa com sucesso!');
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Caixa;
use App\Models\Desbravador;
use App\Models\Mensalidade;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class MensalidadeController extends Controller
{
    /**
     * Lista as mensalidades com filtros.
     */
    public function index(Request $request)
    {
        Gate::authorize('financeiro');

        $mes = $request->input('mes', date('m'));
        $ano = $request->input('ano', date('Y'));

        // OTIMIZAÇÃO: Eager Loading com 'desbravador.unidade' para evitar N+1
        // e permitir exibir a unidade na listagem sem custo de performance.
        $mensalidades = Mensalidade::with(['desbravador.unidade'])
            ->where('mes', $mes)
            ->where('ano', $ano)
            ->get()
            ->sortBy('desbravador.nome'); // Ordenação via Collection para manter performance

        // Cálculos do Mês (Feitos na coleção carregada para evitar novas queries)
        $valorRecebido = $mensalidades->where('status', 'pago')->sum('valor');
        $valorPendente = $mensalidades->where('status', 'pendente')->sum('valor');
        $totalPago = $mensalidades->where('status', 'pago')->count();
        $totalPendente = $mensalidades->where('status', 'pendente')->count();

        // Cálculo de Inadimplência Histórica (Query separada necessária pois olha para todo o histórico)
        $totalInadimplenteGeral = Mensalidade::inadimplentes()->sum('valor');
        $qtdInadimplentes = Mensalidade::inadimplentes()->count();

        return view('financeiro.mensalidades.index', compact(
            'mensalidades',
            'mes',
            'ano',
            'valorRecebido',
            'valorPendente',
            'totalPago',
            'totalPendente',
            'totalInadimplenteGeral',
            'qtdInadimplentes'
        ));
    }

    /**
     * Gera mensalidades em massa para todos os desbravadores.
     */
    public function gerarMassivo(Request $request)
    {
        Gate::authorize('financeiro');

        $request->validate([
            'mes' => 'required|integer|min:1|max:12',
            'ano' => 'required|integer|min:2020',
            'valor' => 'required|numeric|min:0',
        ]);

        // Apenas desbravadores ativos devem receber cobrança
        $desbravadores = Desbravador::where('ativo', true)->get();
        $count = 0;

        foreach ($desbravadores as $dbv) {
            // Verifica se já existe para não duplicar
            $exists = Mensalidade::where('desbravador_id', $dbv->id)
                ->where('mes', $request->mes)
                ->where('ano', $request->ano)
                ->exists();

            if (! $exists) {
                Mensalidade::create([
                    'desbravador_id' => $dbv->id,
                    'mes' => $request->mes,
                    'ano' => $request->ano,
                    'valor' => $request->valor,
                    'status' => 'pendente',
                ]);
                $count++;
            }
        }

        return back()->with('success', "$count mensalidades geradas com sucesso!");
    }

    /**
     * Realiza o pagamento e lança no caixa automaticamente.
     */
    public function pagar($id)
    {
        Gate::authorize('financeiro');

        $mensalidade = Mensalidade::with('desbravador')->findOrFail($id);

        if ($mensalidade->status === 'pago') {
            return back()->with('error', 'Esta mensalidade já consta como paga.');
        }

        // Transação: Tudo ou nada. Se falhar o caixa, não baixa a mensalidade.
        DB::transaction(function () use ($mensalidade) {

            // 1. Atualiza Status da Mensalidade
            $mensalidade->update([
                'status' => 'pago',
                'data_pagamento' => Carbon::now(),
            ]);

            // 2. Lança Entrada no Caixa
            Caixa::create([
                'descricao' => 'Mensalidade '.str_pad($mensalidade->mes, 2, '0', STR_PAD_LEFT).'/'.$mensalidade->ano.' - '.$mensalidade->desbravador->nome,
                'tipo' => 'entrada',
                'categoria' => 'Mensalidade', // Adicionando categoria automaticamente
                'valor' => $mensalidade->valor,
                'data_movimentacao' => Carbon::now(),
            ]);
        });

        return back()->with('success', 'Pagamento recebido e lançado no caixa com sucesso!');
    }
}

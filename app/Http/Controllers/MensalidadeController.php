<?php

namespace App\Http\Controllers;

use App\Models\Caixa;
use App\Models\Desbravador;
use App\Models\Mensalidade;
use App\Models\Unidade;
use App\Services\ClubContext;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class MensalidadeController extends Controller
{
    public function index(Request $request)
    {
        Gate::authorize('financeiro');

        $clubId = ClubContext::currentClubId();
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

        $clubId = ClubContext::currentClubId();

        // Sem clube ativo (platform admin sem impersonação) o ClubScope não filtra
        // e o insert espalharia mensalidades órfãs por todos os clubes. Fail-closed.
        abort_unless($clubId, 403);

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
                'club_id' => $clubId, // insert() em massa não dispara o auto-fill do BelongsToTenant
                'mes' => (int) $request->mes,
                'ano' => (int) $request->ano,
                // Grava como string decimal de 2 casas (coluna decimal(10,2)). O
                // insert() em massa nao passa pelo cast decimal:2 do model, entao
                // formatamos aqui para nao persistir um float impreciso.
                'valor' => number_format((float) $request->valor, 2, '.', ''),
                'status' => 'pendente',
                // insert() também não dispara RegistraAutoria — preenchemos a autoria manualmente.
                'created_by' => auth()->id(),
                'updated_by' => auth()->id(),
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

    /**
     * Preview (dry-run) de quantas mensalidades seriam criadas. Usado pelo modal
     * antes de confirmar a geração em lote — não persiste nada.
     */
    public function previewMassivo(Request $request): \Illuminate\Http\JsonResponse
    {
        Gate::authorize('financeiro');

        $request->validate([
            'mes' => 'required|integer|min:1|max:12',
            'ano' => 'required|integer|min:2020',
            'valor' => 'required|numeric|min:0',
        ]);

        $clubId = ClubContext::currentClubId();
        abort_unless($clubId, 403);

        $ids = Desbravador::ativos()->pluck('id');

        $existentes = Mensalidade::whereIn('desbravador_id', $ids)
            ->where('mes', $request->mes)
            ->where('ano', $request->ano)
            ->count();

        $novas = max(0, $ids->count() - $existentes);

        return response()->json([
            'total_ativos' => $ids->count(),
            'ja_existem' => $existentes,
            'serao_criadas' => $novas,
            'valor_formatado' => 'R$ '.number_format((float) $request->valor, 2, ',', '.'),
            'competencia' => sprintf('%02d/%d', $request->mes, $request->ano),
        ]);
    }

    public function pagar(Request $request, $id)
    {
        Gate::authorize('financeiro');

        $clubId = ClubContext::currentClubId();

        // Garante que a mensalidade pertence ao clube do usuário.
        $mensalidade = Mensalidade::doClube($clubId)
            ->with('desbravador.unidade')
            ->findOrFail($id);

        if ($mensalidade->status === 'pago') {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Esta mensalidade já consta como paga.'], 422);
            }

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
                'mensalidade_id' => $mensalidade->id,
            ]);
        });

        $mensagem = 'Pagamento recebido e lançado no caixa com sucesso!';

        // Requisição AJAX (atualização parcial, sem recarregar a tela): devolve o
        // HTML atualizado do card + os totais recalculados do mês.
        if ($request->expectsJson()) {
            return response()->json([
                'message' => $mensagem,
                'id' => $mensalidade->id,
                'card' => view('financeiro.mensalidades._card', ['m' => $mensalidade])->render(),
                'row' => view('financeiro.mensalidades._row', ['m' => $mensalidade])->render(),
                'resumo' => $this->resumoMes($clubId, (int) $mensalidade->mes, (int) $mensalidade->ano),
            ]);
        }

        return back()->with('success', $mensagem);
    }

    /**
     * Estorna um pagamento: volta a mensalidade para `pendente` e remove a entrada
     * de caixa correspondente (o CaixaObserver registra o `excluido` na auditoria).
     */
    public function estornar(Request $request, $id)
    {
        Gate::authorize('financeiro');

        $clubId = ClubContext::currentClubId();

        $mensalidade = Mensalidade::doClube($clubId)
            ->with('desbravador.unidade')
            ->findOrFail($id);

        if ($mensalidade->status !== 'pago') {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Só é possível estornar mensalidades pagas.'], 422);
            }

            return back()->with('error', 'Só é possível estornar mensalidades pagas.');
        }

        DB::transaction(function () use ($mensalidade, $clubId) {
            // Remove a entrada de caixa gerada pelo pagamento. Prioriza o vínculo
            // direto; cai no casamento por categoria/valor apenas para lançamentos
            // anteriores à coluna mensalidade_id.
            $entrada = Caixa::where('club_id', $clubId)
                ->where('mensalidade_id', $mensalidade->id)
                ->first();

            if (! $entrada) {
                $entrada = Caixa::where('club_id', $clubId)
                    ->whereNull('mensalidade_id')
                    ->where('tipo', 'entrada')
                    ->where('categoria', 'Mensalidade')
                    ->where('valor', $mensalidade->valor)
                    ->where('descricao', 'Mensalidade '.str_pad($mensalidade->mes, 2, '0', STR_PAD_LEFT).'/'.$mensalidade->ano.' - '.$mensalidade->desbravador->nome)
                    ->first();
            }

            $entrada?->delete();

            $mensalidade->update([
                'status' => 'pendente',
                'data_pagamento' => null,
            ]);
        });

        $mensagem = 'Pagamento estornado e entrada removida do caixa.';

        if ($request->expectsJson()) {
            return response()->json([
                'message' => $mensagem,
                'id' => $mensalidade->id,
                'card' => view('financeiro.mensalidades._card', ['m' => $mensalidade])->render(),
                'row' => view('financeiro.mensalidades._row', ['m' => $mensalidade])->render(),
                'resumo' => $this->resumoMes($clubId, (int) $mensalidade->mes, (int) $mensalidade->ano),
            ]);
        }

        return back()->with('success', $mensagem);
    }

    /**
     * Totais do mês (recebido/pendente em R$ formatado e contagens) usados pela
     * atualização parcial após confirmar um pagamento. Fonte única de verdade no
     * servidor — o cliente apenas reflete os números recebidos.
     */
    private function resumoMes(int $clubId, int $mes, int $ano): array
    {
        $mensalidades = Mensalidade::doClube($clubId)
            ->where('mes', $mes)
            ->where('ano', $ano)
            ->get(['status', 'valor']);

        return [
            'valorRecebido' => number_format($mensalidades->where('status', 'pago')->sum('valor'), 2, ',', '.'),
            'valorPendente' => number_format($mensalidades->where('status', 'pendente')->sum('valor'), 2, ',', '.'),
            'totalPago' => $mensalidades->where('status', 'pago')->count(),
            'totalPendente' => $mensalidades->where('status', 'pendente')->count(),
        ];
    }
}

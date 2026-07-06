<?php

namespace App\Services;

use App\Models\Desbravador;
use App\Models\Evento;
use Illuminate\Support\Facades\DB;

/**
 * Regras de inscrição de desbravadores em eventos e a reconciliação financeira
 * correspondente (lançamento/estorno no caixa quando o evento tem valor).
 *
 * Antes esta lógica — lock do pivô, alternância de `pago` e create/estorno de
 * Caixa — vivia inline no EventoController, misturada com resposta AJAX
 * (Candidato C, seam vazado Eventos ↔ Financeiro). Agora o invariante
 * pivô + caixa fica concentrado atrás deste seam e testável em unidade.
 */
class InscricaoEventoService
{
    public function __construct(private readonly LancamentoCaixaService $caixa) {}

    /**
     * Define o status `pago` de uma inscrição, lançando a movimentação no caixa
     * quando o evento é pago (entrada ao marcar, estorno ao desmarcar).
     *
     * @return array{novo_status:bool,status_alterado:bool,movimentacao_registrada:bool}|null
     *                                                                                        null quando o desbravador não está inscrito no evento.
     */
    public function definirPago(Evento $evento, Desbravador $desbravador, bool $pago): ?array
    {
        return DB::transaction(function () use ($evento, $desbravador, $pago) {
            $pivot = DB::table('desbravador_evento')
                ->where('evento_id', $evento->id)
                ->where('desbravador_id', $desbravador->id)
                ->lockForUpdate()
                ->first();

            if (! $pivot) {
                return null;
            }

            if ((bool) $pivot->pago === $pago) {
                return ['novo_status' => $pago, 'status_alterado' => false, 'movimentacao_registrada' => false];
            }

            $evento->desbravadores()->updateExistingPivot($desbravador->id, ['pago' => $pago]);

            $movimentou = $evento->valor > 0;
            if ($movimentou) {
                $this->lancarMovimentacao($evento, $desbravador, $pago);
            }

            return ['novo_status' => $pago, 'status_alterado' => true, 'movimentacao_registrada' => $movimentou];
        });
    }

    /**
     * Remove a inscrição. Se estava paga e o evento tem valor, estorna o caixa.
     */
    public function remover(Evento $evento, Desbravador $desbravador): void
    {
        DB::transaction(function () use ($evento, $desbravador) {
            $pivot = DB::table('desbravador_evento')
                ->where('evento_id', $evento->id)
                ->where('desbravador_id', $desbravador->id)
                ->lockForUpdate()
                ->first();

            if (! $pivot) {
                return;
            }

            if ($pivot->pago && $evento->valor > 0) {
                $this->lancarMovimentacao($evento, $desbravador, false);
            }

            $evento->desbravadores()->detach($desbravador->id);
        });
    }

    /**
     * Indica se remover esta inscrição envolve estorno financeiro (usado pelo
     * controller para exigir a permissão `financeiro` antes de remover um pago).
     */
    public function removerExigeFinanceiro(Evento $evento, Desbravador $desbravador): bool
    {
        if ($evento->valor <= 0) {
            return false;
        }

        return (bool) DB::table('desbravador_evento')
            ->where('evento_id', $evento->id)
            ->where('desbravador_id', $desbravador->id)
            ->value('pago');
    }

    private function lancarMovimentacao(Evento $evento, Desbravador $desbravador, bool $pago): void
    {
        if ($pago) {
            $this->caixa->entrada('Evento', "Evento: {$evento->nome} - {$desbravador->nome}", $evento->valor);
        } else {
            $this->caixa->saida('Evento', "Estorno Evento: {$evento->nome} - {$desbravador->nome}", $evento->valor);
        }
    }
}

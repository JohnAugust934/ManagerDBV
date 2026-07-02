<?php

namespace App\Observers;

use App\Models\Caixa;
use App\Models\CaixaAuditLog;

/**
 * Trilha de auditoria financeira do caixa (Candidato F).
 *
 * Antes cada mutação era auditada à mão em CaixaController (store/update/destroy),
 * com o par retryOnDeadlock + CaixaAuditLog::registrar copiado três vezes e um
 * registro manual no destroy. Concentrar no observer remove a duplicação e passa
 * a auditar TODA movimentação de caixa — inclusive os lançamentos criados por
 * pagamento de mensalidade/evento (via LancamentoCaixaService), que antes ficavam
 * sem trilha.
 */
class CaixaObserver
{
    public function created(Caixa $caixa): void
    {
        CaixaAuditLog::registrar('criado', $caixa, null, $this->snapshot($caixa));
    }

    public function updated(Caixa $caixa): void
    {
        // Durante o evento 'updated' o getOriginal() ainda reflete os valores
        // anteriores (o syncOriginal só ocorre em finishSave, depois deste evento).
        $antes = $this->snapshotValues(
            $caixa->getOriginal('descricao'),
            $caixa->getOriginal('valor'),
            $caixa->getOriginal('tipo'),
            $caixa->getOriginal('categoria'),
            $caixa->getOriginal('data_movimentacao'),
        );

        CaixaAuditLog::registrar('editado', $caixa, $antes, $this->snapshot($caixa));
    }

    public function deleted(Caixa $caixa): void
    {
        // O model deletado mantém os atributos em memória — registrar('excluido')
        // resolve caixa_id/club_id a partir deles.
        CaixaAuditLog::registrar('excluido', $caixa, $this->snapshot($caixa), null);
    }

    private function snapshot(Caixa $caixa): array
    {
        return $this->snapshotValues(
            $caixa->descricao,
            $caixa->valor,
            $caixa->tipo,
            $caixa->categoria,
            $caixa->data_movimentacao,
        );
    }

    private function snapshotValues($descricao, $valor, $tipo, $categoria, $dataMovimentacao): array
    {
        return [
            'descricao' => $descricao,
            'valor' => (string) $valor,
            'tipo' => $tipo,
            'categoria' => $categoria,
            'data_movimentacao' => $dataMovimentacao?->format('Y-m-d'),
        ];
    }
}

<?php

namespace App\Observers;

use App\Models\Caixa;
use App\Models\CaixaAuditLog;

/**
 * Garante que TODA movimentação de caixa tenha trilha de auditoria — inclusive as
 * geradas fora do CaixaController (ex.: pagamento/estorno de mensalidade). Antes a
 * auditoria vivia só no controller, deixando entradas de mensalidade sem registro.
 */
class CaixaObserver
{
    public function created(Caixa $caixa): void
    {
        CaixaAuditLog::registrar('criado', $caixa, null, $caixa->dadosAuditaveis());
    }

    public function updated(Caixa $caixa): void
    {
        CaixaAuditLog::registrar('editado', $caixa, $caixa->dadosAuditaveisOriginais(), $caixa->dadosAuditaveis());
    }

    public function deleted(Caixa $caixa): void
    {
        CaixaAuditLog::registrar('excluido', $caixa, $caixa->dadosAuditaveis(), null);
    }
}

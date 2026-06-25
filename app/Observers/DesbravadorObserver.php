<?php

namespace App\Observers;

use App\Models\Desbravador;
use App\Services\LgpdService;

class DesbravadorObserver
{
    /**
     * Quando ligado, suprime o registro automático de consentimento neste
     * Observer. Usado por fluxos que já gravam um ROPA explícito para o mesmo
     * evento (ex.: o comando de anonimização grava a ação 'anonimizacao' e não
     * deve gerar também uma revogação de consentimento).
     */
    public static bool $suprimirConsentimento = false;

    /**
     * Cadastro de desbravador com consentimento marcado gera ROPA de consentimento.
     */
    public function created(Desbravador $desbravador): void
    {
        if (self::$suprimirConsentimento) {
            return;
        }

        if ($desbravador->consentimento_lgpd) {
            LgpdService::registrar('consentimento', 'desbravador', $desbravador->id, [
                'operacao' => 'concessao',
                'responsavel' => $desbravador->consentimento_lgpd_responsavel,
            ]);
        }
    }

    /**
     * Mudança no flag de consentimento gera ROPA de concessão ou revogação.
     */
    public function updated(Desbravador $desbravador): void
    {
        if (self::$suprimirConsentimento) {
            return;
        }

        if (! $desbravador->wasChanged('consentimento_lgpd')) {
            return;
        }

        $operacao = $desbravador->consentimento_lgpd ? 'concessao' : 'revogacao';

        LgpdService::registrar('consentimento', 'desbravador', $desbravador->id, [
            'operacao' => $operacao,
            'responsavel' => $desbravador->consentimento_lgpd_responsavel,
        ]);
    }

    /**
     * Exclusão de desbravador gera ROPA de exclusão.
     */
    public function deleted(Desbravador $desbravador): void
    {
        LgpdService::registrar('exclusao', 'desbravador', $desbravador->id, [
            'nome' => $desbravador->nome,
        ]);
    }
}

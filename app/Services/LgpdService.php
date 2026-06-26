<?php

namespace App\Services;

use App\Models\LgpdRegistro;
use Illuminate\Http\Request;

class LgpdService
{
    /**
     * Registra uma operação de tratamento de dados pessoais (ROPA — Art. 37 LGPD).
     *
     * @param  string  $acao  'acesso'|'exportacao'|'exclusao'|'consentimento'|'retificacao'|'oposicao_imagem'
     * @param  string  $entidade  'desbravador'|'ficha_medica'|'frequencia'
     */
    public static function registrar(
        string $acao,
        string $entidade,
        ?int $entidadeId = null,
        array $metadados = [],
        ?Request $request = null,
    ): void {
        $user = auth()->user();

        LgpdRegistro::create([
            'club_id' => $user?->club_id,
            'user_id' => $user?->id,
            'acao' => $acao,
            'entidade' => $entidade,
            'entidade_id' => $entidadeId,
            'ip_origem' => $request?->ip() ?? request()->ip(),
            'metadados' => empty($metadados) ? null : $metadados,
        ]);
    }
}

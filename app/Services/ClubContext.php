<?php

namespace App\Services;

use App\Models\Club;

/**
 * Resolve o clube ativo da requisição, suportando impersonação de clube
 * por administradores de plataforma (super admin) via sessão.
 *
 * Regras:
 *  - Usuário comum: sempre o club_id do próprio usuário.
 *  - Platform admin impersonando: o club_id guardado em session('club_context').
 *  - Platform admin sem contexto: null (sem filtro — enxerga tudo).
 */
class ClubContext
{
    public const SESSION_KEY = 'club_context';

    /**
     * club_id ativo para a requisição atual (ou null = sem filtro de clube).
     */
    public static function currentClubId(): ?int
    {
        $user = auth()->user();

        if (! $user) {
            return null;
        }

        if ($user->is_platform_admin) {
            // Impersonando um clube específico
            if (session()->has(self::SESSION_KEY)) {
                return (int) session(self::SESSION_KEY);
            }

            // Sem contexto ativo: vê todos os clubes
            return null;
        }

        return $user->club_id;
    }

    public static function isPlatformAdmin(): bool
    {
        return (bool) auth()->user()?->is_platform_admin;
    }

    public static function isImpersonating(): bool
    {
        return self::isPlatformAdmin() && session()->has(self::SESSION_KEY);
    }

    public static function currentClub(): ?Club
    {
        $id = self::currentClubId();

        return $id ? Club::find($id) : null;
    }
}

<?php

namespace App\Services;

use App\Models\Club;
use Closure;

/**
 * Resolve o clube ativo da requisição, suportando impersonação de clube
 * por administradores de plataforma (super admin) via sessão.
 *
 * Regras:
 *  - Override explícito (jobs/comandos via actAs): sempre vence.
 *  - Usuário comum: sempre o club_id do próprio usuário.
 *  - Platform admin impersonando: o club_id guardado em session('club_context').
 *  - Platform admin sem contexto: null (sem filtro — enxerga tudo).
 */
class ClubContext
{
    public const SESSION_KEY = 'club_context';

    /** Override de tenant para contextos sem HTTP (jobs, comandos, seeders). */
    private static ?int $overrideClubId = null;

    private static bool $overriding = false;

    /**
     * club_id ativo para a requisição atual (ou null = sem filtro de clube).
     */
    public static function currentClubId(): ?int
    {
        if (self::$overriding) {
            return self::$overrideClubId;
        }

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

    /**
     * Executa o callback com um tenant FIXO, mesmo fora de uma requisição HTTP
     * (jobs, comandos, seeders). Restaura o contexto anterior ao fim — inclusive
     * em caso de exceção — e é aninhável.
     *
     * @template TReturn
     *
     * @param  Closure(): TReturn  $callback
     * @return TReturn
     */
    public static function actAs(int $clubId, Closure $callback): mixed
    {
        $previousOverriding = self::$overriding;
        $previousClubId = self::$overrideClubId;

        self::$overriding = true;
        self::$overrideClubId = $clubId;

        try {
            return $callback();
        } finally {
            self::$overriding = $previousOverriding;
            self::$overrideClubId = $previousClubId;
        }
    }

    /** Há um override de tenant ativo (via actAs)? */
    public static function hasOverride(): bool
    {
        return self::$overriding;
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

<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Bloqueia o acesso de usuários cujo clube foi DESATIVADO (is_active = false).
 *
 * O platform admin (club_id null) é isento — precisa acessar o painel e o modo
 * suporte mesmo para reativar um clube. Usuários em onboarding (ainda sem clube)
 * também passam. Se um clube é desativado com usuários logados, o próximo request
 * deles cai aqui: são deslogados e veem a mensagem.
 */
class EnsureClubIsActive
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->club_id) {
            $club = $user->club;

            if ($club && ! $club->is_active) {
                Auth::guard('web')->logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()->route('login')->withErrors([
                    'email' => 'O acesso a este clube foi desativado. Fale com o administrador da plataforma.',
                ]);
            }
        }

        return $next($request);
    }
}

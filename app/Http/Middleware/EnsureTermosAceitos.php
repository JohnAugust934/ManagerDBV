<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Obriga o aceite dos Termos de Uso / Política de Privacidade (LGPD Art. 7/8).
 *
 * Novos usuários já aceitam no cadastro por convite (termos_aceitos_em é
 * preenchido). Usuários criados ANTES dessa exigência têm termos_aceitos_em
 * nulo: no próximo acesso são desviados para a tela de aceite e só voltam a
 * usar o sistema após aceitar. A própria tela de aceite e o logout ficam fora
 * deste grupo de middleware, evitando loop de redirecionamento.
 */
class EnsureTermosAceitos
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->termos_aceitos_em === null) {
            // Defesa contra loop caso a rota de aceite passe a herdar este grupo.
            if ($request->routeIs('termos.aceitar', 'termos.aceitar.store', 'logout')) {
                return $next($request);
            }

            // Requisições AJAX/JSON recebem 409 para o front tratar; navegação normal é redirecionada.
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'É necessário aceitar os Termos de Uso e a Política de Privacidade para continuar.',
                    'redirect' => route('termos.aceitar'),
                ], 409);
            }

            return redirect()->guest(route('termos.aceitar'));
        }

        return $next($request);
    }
}

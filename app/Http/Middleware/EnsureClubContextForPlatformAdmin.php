<?php

namespace App\Http\Middleware;

use App\Services\ClubContext;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

/**
 * Mantém o platform admin SEM clube ativo restrito ao painel da plataforma.
 *
 * Sem um clube em contexto, as telas escopadas por clube mostrariam dados de
 * todos os clubes mesclados (fail-open). Em vez disso, o platform admin é levado
 * a /platform para escolher um clube (modo suporte). Telas que não dependem de
 * clube (próprio painel, backups, perfil, ajuda) continuam acessíveis.
 */
class EnsureClubContextForPlatformAdmin
{
    /**
     * Prefixos/nomes de rota liberados mesmo sem clube em contexto.
     */
    // 'usuarios.'/'invites.' liberados para o admin gerir a EQUIPE DA PLATAFORMA
    // (outros admins de plataforma) sem precisar entrar em modo suporte. Os
    // controllers garantem o escopo: sem clube ativo, so enxergam platform admins.
    private const ALLOWED_PREFIXES = ['platform.', 'backups.', 'usuarios.', 'invites.'];

    private const ALLOWED_NAMES = [
        'profile.edit',
        'profile.update',
        'profile.destroy',
        'logout',
        'sobre',
        'manual.sistema',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // Só atua sobre platform admin que NÃO está em modo suporte (sem clube ativo).
        if (! $user || ! $user->is_platform_admin || ClubContext::isImpersonating()) {
            return $next($request);
        }

        $routeName = $request->route()?->getName() ?? '';

        if ($this->isAllowed($routeName)) {
            return $next($request);
        }

        return redirect()->route('platform.index')
            ->with('info', 'Selecione um clube (modo suporte) para acessar as telas do clube.');
    }

    private function isAllowed(string $routeName): bool
    {
        foreach (self::ALLOWED_PREFIXES as $prefix) {
            if (Str::startsWith($routeName, $prefix)) {
                return true;
            }
        }

        return in_array($routeName, self::ALLOWED_NAMES, true);
    }
}

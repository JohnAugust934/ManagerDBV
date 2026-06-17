<?php

namespace App\Models\Scopes;

use App\Services\ClubContext;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

/**
 * Aplica isolamento multi-tenancy em Mensalidade via a cadeia
 * desbravador.unidade.club_id. Mensalidade não tem club_id direto.
 *
 * Espelha a lógica do scopeDoClube() (mantido por compatibilidade), mas
 * roda automaticamente em todas as queries do model.
 */
class MensalidadeClubScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        // Sem usuário autenticado (seeders, console, factories): não filtra.
        if (! auth()->check()) {
            return;
        }

        $clubId = ClubContext::currentClubId();

        if ($clubId !== null) {
            $builder->whereHas('desbravador.unidade', fn (Builder $q) => $q->where('club_id', $clubId));

            return;
        }

        // Sem clube ativo: platform admin enxerga tudo; usuário comum, nada.
        if (! ClubContext::isPlatformAdmin()) {
            $builder->whereRaw('1 = 0');
        }
    }
}

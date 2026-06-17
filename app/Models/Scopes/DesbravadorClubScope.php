<?php

namespace App\Models\Scopes;

use App\Services\ClubContext;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

/**
 * Aplica isolamento multi-tenancy em Desbravador via relação com Unidade.
 * Desbravador não tem club_id direto — o vínculo passa por unidade.club_id.
 */
class DesbravadorClubScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        // Sem usuário autenticado (seeders, console, factories): não filtra.
        if (! auth()->check()) {
            return;
        }

        $clubId = ClubContext::currentClubId();

        if ($clubId !== null) {
            $builder->whereHas('unidade', fn (Builder $q) => $q->where('club_id', $clubId));

            return;
        }

        // Sem clube ativo: platform admin enxerga tudo; usuário comum, nada.
        if (! ClubContext::isPlatformAdmin()) {
            $builder->whereRaw('1 = 0');
        }
    }
}

<?php

namespace App\Models\Scopes;

use App\Services\ClubContext;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

/**
 * Aplica isolamento multi-tenancy automaticamente por club_id.
 * Use em modelos que possuem a coluna club_id diretamente.
 */
class ClubScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        // Override explícito (jobs/comandos/seeders via ClubContext::actAs): filtra
        // pelo tenant declarado, mesmo sem requisição HTTP autenticada.
        if (ClubContext::hasOverride()) {
            $builder->where($model->getTable().'.club_id', ClubContext::currentClubId());

            return;
        }

        // Sem usuário autenticado e sem override (seeders, console, factories): não filtra.
        if (! auth()->check()) {
            return;
        }

        $clubId = ClubContext::currentClubId();

        if ($clubId !== null) {
            $builder->where($model->getTable().'.club_id', $clubId);

            return;
        }

        // Sem clube ativo: platform admin enxerga tudo; usuário comum, nada.
        if (! ClubContext::isPlatformAdmin()) {
            $builder->whereRaw('1 = 0');
        }
    }
}

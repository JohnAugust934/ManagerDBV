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
        // Sem usuário autenticado (seeders, console, factories): não filtra.
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

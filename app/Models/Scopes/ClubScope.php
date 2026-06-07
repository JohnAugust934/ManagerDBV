<?php

namespace App\Models\Scopes;

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
        if (! auth()->check()) {
            return;
        }

        $clubId = auth()->user()->club_id;

        if (! $clubId) {
            return;
        }

        $builder->where($model->getTable().'.club_id', $clubId);
    }
}

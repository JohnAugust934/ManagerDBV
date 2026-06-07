<?php

namespace App\Models\Scopes;

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
        if (! auth()->check()) {
            return;
        }

        $clubId = auth()->user()->club_id;

        if (! $clubId) {
            return;
        }

        $builder->whereHas('unidade', fn (Builder $q) => $q->where('club_id', $clubId));
    }
}

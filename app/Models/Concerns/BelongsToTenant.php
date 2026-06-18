<?php

namespace App\Models\Concerns;

use App\Models\Club;
use App\Models\Scopes\ClubScope;
use App\Services\ClubContext;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Isolamento multi-tenant para models com coluna `club_id` direta.
 *
 * Faz duas coisas:
 *  1. Registra o global scope {@see ClubScope} (filtra por club_id ativo).
 *  2. Preenche `club_id` automaticamente ao criar, a partir do clube ativo
 *     ({@see ClubContext}). Sem contexto (seeders, console, jobs sem auth),
 *     tenta derivar do registro-pai via `resolveClubIdFromParent()`, se o model
 *     a definir (ex.: Desbravador → unidade; Frequencia/Mensalidade → desbravador).
 *
 * Não torna `club_id` mass-assignable por conta própria — o model decide se
 * inclui em $fillable. Um club_id explícito (import, seeder) tem precedência
 * sobre o auto-preenchimento.
 */
trait BelongsToTenant
{
    public static function bootBelongsToTenant(): void
    {
        static::addGlobalScope(new ClubScope);

        static::creating(function (Model $model): void {
            if (! empty($model->getAttribute('club_id'))) {
                return;
            }

            $clubId = ClubContext::currentClubId();

            if ($clubId === null && method_exists($model, 'resolveClubIdFromParent')) {
                $clubId = $model->resolveClubIdFromParent();
            }

            if ($clubId !== null) {
                $model->setAttribute('club_id', $clubId);
            }
        });
    }

    public function club(): BelongsTo
    {
        return $this->belongsTo(Club::class);
    }
}

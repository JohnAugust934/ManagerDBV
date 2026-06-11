<?php

namespace App\Models\Concerns;

use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Preenche automaticamente created_by/updated_by com o usuário autenticado.
 *
 * As colunas são anuláveis: ações sem usuário logado (seeders, comandos agendados,
 * factories) simplesmente não registram autoria, sem quebrar nada.
 */
trait RegistraAutoria
{
    public static function bootRegistraAutoria(): void
    {
        static::creating(function ($model) {
            $userId = auth()->id();

            if ($userId) {
                if (empty($model->created_by)) {
                    $model->created_by = $userId;
                }
                if (empty($model->updated_by)) {
                    $model->updated_by = $userId;
                }
            }
        });

        static::updating(function ($model) {
            $userId = auth()->id();

            if ($userId) {
                $model->updated_by = $userId;
            }
        });
    }

    public function criadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function atualizadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}

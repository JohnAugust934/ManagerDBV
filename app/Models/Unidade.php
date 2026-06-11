<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Unidade extends Model
{
    use HasFactory;

    protected $fillable = [
        'nome',
        'grito_guerra',
        'conselheiro',
        'conselheiro_user_id',
        'club_id',
        'no_ranking',
    ];

    protected $casts = [
        'no_ranking' => 'boolean',
    ];

    // Relacionamento: Uma unidade tem vários desbravadores
    public function desbravadores(): HasMany
    {
        return $this->hasMany(Desbravador::class);
    }

    // Conselheiro responsável vinculado a um usuário do sistema (opcional).
    public function conselheiroUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'conselheiro_user_id');
    }

    public function getPontuacaoTotalAttribute()
    {
        return $this->desbravadores->sum(function ($desbravador) {
            return $desbravador->total_pontos;
        });
    }

    // Opcional: Média por membro (para ser justo com unidades menores)
    public function getPontuacaoMediaAttribute()
    {
        if ($this->desbravadores->count() === 0) {
            return 0;
        }

        return $this->pontuacao_total / $this->desbravadores->count();
    }
}

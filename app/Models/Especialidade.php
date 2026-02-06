<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Especialidade extends Model
{
    use HasFactory;

    protected $fillable = [
        'nome',
        'area',
        'cor_hex', // Usamos 'cor_hex' para alinhar com o form e controller
    ];

    /**
     * Relacionamento com Desbravadores (Muitos para Muitos)
     */
    public function desbravadores(): BelongsToMany
    {
        // O segundo argumento é o nome da tabela pivô criada na migração
        return $this->belongsToMany(Desbravador::class, 'desbravador_especialidade');
    }

    /**
     * Relacionamento com Requisitos (Um para Muitos)
     */
    public function requisitos(): HasMany
    {
        return $this->hasMany(Requisito::class);
    }
}

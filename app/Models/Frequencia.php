<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Frequencia extends Model
{
    use HasFactory;

    protected $fillable = [
        'desbravador_id',
        'data',
        'presente',
        'pontual',
        'biblia',
        'uniforme',
    ];

    protected $casts = [
        'data' => 'date',
        'presente' => 'boolean',
        'pontual' => 'boolean',
        'biblia' => 'boolean',
        'uniforme' => 'boolean',
    ];

    public function desbravador(): BelongsTo
    {
        return $this->belongsTo(Desbravador::class);
    }

    public function columnValues(): HasMany
    {
        return $this->hasMany(FrequenciaColumnValue::class);
    }

    public function getPontosAttribute(): int
    {
        if ($this->relationLoaded('columnValues') && $this->columnValues->isNotEmpty()) {
            return (int) $this->columnValues->sum('points_awarded');
        }

        if ($this->columnValues()->exists()) {
            return (int) $this->columnValues()->sum('points_awarded');
        }

        return $this->calcularPontosLegado();
    }

    private function calcularPontosLegado(): int
    {
        return ($this->presente ? 10 : 0)
            + ($this->pontual ? 5 : 0)
            + ($this->biblia ? 5 : 0)
            + ($this->uniforme ? 10 : 0);
    }
}

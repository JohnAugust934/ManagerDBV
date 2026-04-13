<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Schema;

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
        if (! Schema::hasTable('frequencia_column_values')) {
            $pontos = 0;
            if ($this->presente) {
                $pontos += 10;
            }
            if ($this->pontual) {
                $pontos += 5;
            }
            if ($this->biblia) {
                $pontos += 5;
            }
            if ($this->uniforme) {
                $pontos += 10;
            }

            return $pontos;
        }

        if ($this->relationLoaded('columnValues') && $this->columnValues->isNotEmpty()) {
            return (int) $this->columnValues->sum('points_awarded');
        }

        if ($this->columnValues()->exists()) {
            return (int) $this->columnValues()->sum('points_awarded');
        }

        $pontos = 0;
        if ($this->presente) {
            $pontos += 10;
        }
        if ($this->pontual) {
            $pontos += 5;
        }
        if ($this->biblia) {
            $pontos += 5;
        }
        if ($this->uniforme) {
            $pontos += 10;
        }

        return $pontos;
    }
}

<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Frequencia extends Model
{
    use BelongsToTenant, HasFactory;

    protected $fillable = [
        'desbravador_id',
        'club_id',
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

    /**
     * Sem contexto de clube ativo, herda o clube do desbravador. Usado pelo
     * trait BelongsToTenant ao criar (ex.: seeders, import).
     */
    public function resolveClubIdFromParent(): ?int
    {
        if (! $this->desbravador_id) {
            return null;
        }

        return Desbravador::withoutGlobalScopes()->whereKey($this->desbravador_id)->value('club_id');
    }

    public function columnValues(): HasMany
    {
        return $this->hasMany(FrequenciaColumnValue::class);
    }

    /**
     * Pontos fixos por coluna no caminho legado (colunas booleanas), também usados
     * como defaults do modo novo (ver App\Services\AttendanceColumnService).
     */
    public const PONTOS_LEGADO = [
        'presente' => 10,
        'pontual' => 5,
        'biblia' => 5,
        'uniforme' => 10,
    ];

    /**
     * Pontuação total desta frequência. FONTE DA VERDADE ÚNICA do ranking — todos
     * os consumidores (RankingService, snapshot de console, PDFs, ficha do
     * desbravador) somam por aqui. Conta apenas colunas MARCADAS (checked); no
     * modo novo isso casa com o breakdown de detalhePontos().
     */
    public function getPontosAttribute(): int
    {
        return $this->detalhePontos()['total'];
    }

    /**
     * Detalhamento da pontuação por chave de coluna fixa
     * (presente/pontual/biblia/uniforme) + 'total'. Some apenas colunas marcadas.
     *
     * Para evitar N+1 no breakdown, o chamador deve carregar 'columnValues.column'
     * (o RankingService já faz). Fallback para o modo legado quando não há
     * column_values.
     *
     * @return array{presente:int,pontual:int,biblia:int,uniforme:int,total:int}
     */
    public function detalhePontos(): array
    {
        $stats = ['presente' => 0, 'pontual' => 0, 'biblia' => 0, 'uniforme' => 0, 'total' => 0];

        $columnValues = $this->relationLoaded('columnValues')
            ? $this->columnValues
            : ($this->exists ? $this->columnValues()->with('column')->get() : collect());

        if ($columnValues->isNotEmpty()) {
            foreach ($columnValues as $columnValue) {
                if (! $columnValue->checked) {
                    continue;
                }

                $points = (int) $columnValue->points_awarded;
                $stats['total'] += $points;

                $columnKey = $columnValue->column?->key;
                if ($columnKey !== null && array_key_exists($columnKey, $stats)) {
                    $stats[$columnKey] += $points;
                }
            }

            return $stats;
        }

        return $this->detalhePontosLegado();
    }

    /**
     * @return array{presente:int,pontual:int,biblia:int,uniforme:int,total:int}
     */
    private function detalhePontosLegado(): array
    {
        $stats = ['presente' => 0, 'pontual' => 0, 'biblia' => 0, 'uniforme' => 0, 'total' => 0];

        foreach (self::PONTOS_LEGADO as $chave => $valor) {
            if ($this->{$chave}) {
                $stats[$chave] = $valor;
                $stats['total'] += $valor;
            }
        }

        return $stats;
    }
}

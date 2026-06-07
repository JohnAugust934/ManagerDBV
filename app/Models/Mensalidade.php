<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Mensalidade extends Model
{
    use HasFactory;

    protected $fillable = [
        'desbravador_id',
        'mes',
        'ano',
        'valor',
        'status',
        'data_pagamento',
    ];

    protected $casts = [
        'data_pagamento' => 'date',
        'valor' => 'decimal:2',
    ];

    public function desbravador(): BelongsTo
    {
        return $this->belongsTo(Desbravador::class);
    }

    /**
     * Scope: filtra mensalidades do clube do usuário autenticado.
     */
    public function scopeDoClube($query, ?int $clubId = null)
    {
        $clubId ??= auth()->user()?->club_id;

        if (! $clubId) {
            return $query->whereRaw('1 = 0');
        }

        return $query->whereHas('desbravador.unidade', fn ($q) => $q->where('club_id', $clubId));
    }

    /**
     * Scope: mensalidades pendentes de meses anteriores ao atual.
     */
    public function scopeInadimplentes($query)
    {
        $now = Carbon::now();

        return $query->where('status', 'pendente')
            ->where(function ($q) use ($now) {
                $q->where('ano', '<', $now->year)
                    ->orWhere(function ($sub) use ($now) {
                        $sub->where('ano', '=', $now->year)
                            ->where('mes', '<', $now->month);
                    });
            });
    }
}

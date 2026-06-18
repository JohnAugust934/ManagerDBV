<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use App\Services\ClubContext;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Mensalidade extends Model
{
    use BelongsToTenant, HasFactory;

    protected $fillable = [
        'desbravador_id',
        'club_id',
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

    /**
     * Scope: filtra mensalidades de um clube (default: clube ativo). Agora usa a
     * coluna club_id direta — o global scope já aplica isto automaticamente; este
     * scope permanece para chamadas explícitas (ex.: console passando um clubId).
     */
    public function scopeDoClube($query, ?int $clubId = null)
    {
        $clubId ??= ClubContext::currentClubId();

        if (! $clubId) {
            return $query->whereRaw('1 = 0');
        }

        return $query->where($this->getTable().'.club_id', $clubId);
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

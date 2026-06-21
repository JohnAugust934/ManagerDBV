<?php

namespace App\Models;

use App\Models\Scopes\ClubScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RelatorioGerado extends Model
{
    protected $fillable = [
        'club_id', 'user_id', 'tipo', 'status', 'arquivo', 'filtros', 'erro', 'expires_at',
    ];

    protected $casts = [
        'filtros' => 'array',
        'expires_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope(new ClubScope);
    }

    public function clube(): BelongsTo
    {
        return $this->belongsTo(Club::class, 'club_id');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function isPronto(): bool
    {
        return $this->status === 'pronto' && ! $this->expirou();
    }

    public function expirou(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    public function tipoLabel(): string
    {
        return match ($this->tipo) {
            'fichas_completas' => 'Fichas Completas',
            'fichas_medicas' => 'Fichas Médicas',
            'financeiro' => 'Relatório Financeiro',
            default => ucfirst(str_replace('_', ' ', $this->tipo)),
        };
    }
}

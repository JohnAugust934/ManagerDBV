<?php

namespace App\Models;

use App\Models\Concerns\RegistraAutoria;
use App\Models\Scopes\ClubScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Caixa extends Model
{
    use HasFactory, RegistraAutoria;

    protected $fillable = [
        'descricao',
        'valor',
        'tipo',
        'data_movimentacao',
        'categoria',
        'club_id',
    ];

    protected $casts = [
        'data_movimentacao' => 'date',
        'valor' => 'decimal:2',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope(new ClubScope);
    }
}

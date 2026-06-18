<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use App\Models\Concerns\RegistraAutoria;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Caixa extends Model
{
    use BelongsToTenant, HasFactory, RegistraAutoria;

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
}

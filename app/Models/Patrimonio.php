<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Patrimonio extends Model
{
    use HasFactory;

    protected $fillable = [
        'item',
        'quantidade',
        'valor_estimado',
        'data_aquisicao',
        'estado_conservacao',
        'local_armazenamento',
        'observacoes'
    ];

    protected $casts = [
        'data_aquisicao' => 'date',
        'valor_estimado' => 'decimal:2',
    ];
}

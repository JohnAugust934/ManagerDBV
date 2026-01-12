<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ata extends Model
{
    use HasFactory;

    protected $fillable = [
        'data_reuniao',
        'tipo',
        'secretario_responsavel',
        'participantes',
        'conteudo'
    ];

    protected $casts = [
        'data_reuniao' => 'date',
    ];
}

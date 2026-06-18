<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ata extends Model
{
    use BelongsToTenant, HasFactory;

    protected $fillable = [
        'club_id',
        'titulo',       // Adicionado
        'data_reuniao',
        'hora_inicio',  // Adicionado
        'hora_fim',     // Adicionado
        'local',        // Adicionado
        'tipo',
        'secretario_responsavel',
        'participantes',
        'conteudo',
    ];

    protected $casts = [
        'data_reuniao' => 'date',
        // Opcional: Casting para Carbon facilita manipulação de horas na view
        'hora_inicio' => 'datetime:H:i',
        'hora_fim' => 'datetime:H:i',
    ];
}

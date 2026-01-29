<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Evento extends Model
{
    use HasFactory;

    protected $fillable = [
        'nome',
        'data_inicio',
        'data_fim',
        'local',
        'valor',
        'descricao'
    ];

    protected $casts = [
        'data_inicio' => 'datetime',
        'data_fim' => 'datetime',
        'valor' => 'decimal:2'
    ];

    public function desbravadores()
    {
        return $this->belongsToMany(Desbravador::class, 'desbravador_evento')
            ->withPivot('pago', 'autorizacao_entregue')
            ->withTimestamps();
    }
}

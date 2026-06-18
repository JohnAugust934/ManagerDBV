<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Ato extends Model
{
    use BelongsToTenant, HasFactory;

    protected $fillable = [
        'club_id',
        'numero',    // Adicionado
        'data',
        'tipo',
        'descricao', // Alterado (removemos descricao_resumida e texto_completo)
        'desbravador_id',
    ];

    protected $casts = [
        'data' => 'date',
    ];

    public function desbravador(): BelongsTo
    {
        return $this->belongsTo(Desbravador::class);
    }
}

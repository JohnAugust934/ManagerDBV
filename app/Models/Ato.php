<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Ato extends Model
{
    use HasFactory;

    protected $fillable = [
        'data',
        'tipo',
        'descricao_resumida',
        'texto_completo',
        'desbravador_id'
    ];

    protected $casts = [
        'data' => 'date',
    ];

    public function desbravador(): BelongsTo
    {
        return $this->belongsTo(Desbravador::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PatrimonioManutencao extends Model
{
    use HasFactory;

    protected $table = 'patrimonio_manutencoes';

    protected $fillable = [
        'patrimonio_id',
        'user_id',
        'data',
        'estado_anterior',
        'estado_novo',
        'descricao',
    ];

    protected $casts = [
        'data' => 'date',
    ];

    public function patrimonio(): BelongsTo
    {
        return $this->belongsTo(Patrimonio::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

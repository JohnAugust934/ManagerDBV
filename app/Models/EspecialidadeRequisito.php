<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EspecialidadeRequisito extends Model
{
    use HasFactory;

    protected $fillable = [
        'especialidade_id',
        'ordem',
        'descricao',
    ];

    public function especialidade(): BelongsTo
    {
        return $this->belongsTo(Especialidade::class);
    }
}

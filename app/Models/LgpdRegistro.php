<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LgpdRegistro extends Model
{
    protected $table = 'lgpd_registros';

    protected $fillable = [
        'club_id',
        'user_id',
        'acao',
        'entidade',
        'entidade_id',
        'ip_origem',
        'metadados',
    ];

    protected $casts = [
        'metadados' => 'array',
    ];

    public function club(): BelongsTo
    {
        return $this->belongsTo(Club::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

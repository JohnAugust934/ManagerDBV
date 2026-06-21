<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CaixaAuditLog extends Model
{
    use BelongsToTenant;

    public $timestamps = false;

    protected $fillable = [
        'caixa_id',
        'club_id',
        'user_id',
        'acao',
        'dados_antes',
        'dados_depois',
        'created_at',
    ];

    protected $casts = [
        'dados_antes' => 'array',
        'dados_depois' => 'array',
        'created_at' => 'datetime',
    ];

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public static function registrar(string $acao, ?Caixa $caixa, ?array $antes, ?array $depois): void
    {
        static::create([
            'caixa_id' => $caixa?->id,
            'club_id' => $caixa?->club_id ?? auth()->user()?->club_id,
            'user_id' => auth()->id(),
            'acao' => $acao,
            'dados_antes' => $antes,
            'dados_depois' => $depois,
            'created_at' => now(),
        ]);
    }
}

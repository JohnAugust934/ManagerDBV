<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Registro de consentimento LGPD (histórico). Um desbravador pode ter vários
 * registros ao longo do tempo, mas só um ativo por vez (aceito e não revogado).
 * Revogar nunca apaga o histórico — apenas encerra o registro atual.
 */
class ConsentimentoPrivacidade extends Model
{
    use BelongsToTenant;

    protected $table = 'consentimentos_privacidade';

    protected $fillable = [
        'club_id',
        'desbravador_id',
        'responsavel_nome',
        'versao_termo',
        'termo_snapshot',
        'aceito_em',
        'aceito_ip',
        'revogado_em',
        'revogado_por',
        'motivo_revogacao',
        'via_fisica_recebida_em',
        'via_fisica_caminho',
    ];

    protected $casts = [
        'aceito_em' => 'datetime',
        'revogado_em' => 'datetime',
        'via_fisica_recebida_em' => 'datetime',
    ];

    public function desbravador(): BelongsTo
    {
        return $this->belongsTo(Desbravador::class);
    }

    /** Sem contexto de clube (console/seeder), deriva o clube pelo desbravador. */
    public function resolveClubIdFromParent(): ?int
    {
        if (! $this->desbravador_id) {
            return null;
        }

        return Desbravador::withoutGlobalScopes()->whereKey($this->desbravador_id)->value('club_id');
    }

    public function estaAtivo(): bool
    {
        return $this->aceito_em !== null && $this->revogado_em === null;
    }
}

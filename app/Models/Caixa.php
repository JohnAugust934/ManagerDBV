<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use App\Models\Concerns\RegistraAutoria;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Caixa extends Model
{
    use BelongsToTenant, HasFactory, RegistraAutoria;

    protected $fillable = [
        'descricao',
        'valor',
        'tipo',
        'data_movimentacao',
        'categoria',
        'club_id',
        'mensalidade_id',
    ];

    protected $casts = [
        'data_movimentacao' => 'date',
        'valor' => 'decimal:2',
    ];

    public function mensalidade(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Mensalidade::class);
    }

    /**
     * Snapshot dos campos relevantes para a trilha de auditoria (estado atual).
     */
    public function dadosAuditaveis(): array
    {
        return [
            'descricao' => $this->descricao,
            'valor' => (string) $this->valor,
            'tipo' => $this->tipo,
            'categoria' => $this->categoria,
            'data_movimentacao' => $this->data_movimentacao?->format('Y-m-d'),
        ];
    }

    /**
     * Mesmo snapshot, porém a partir dos valores originais (antes do update) —
     * usado pelo CaixaObserver para preencher `dados_antes` em edições.
     */
    public function dadosAuditaveisOriginais(): array
    {
        return (new static)->forceFill($this->getOriginal())->dadosAuditaveis();
    }
}

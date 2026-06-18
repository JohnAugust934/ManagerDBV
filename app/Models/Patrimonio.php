<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Patrimonio extends Model
{
    use BelongsToTenant, HasFactory;

    protected $fillable = [
        'item',
        'quantidade',
        'valor_estimado',
        'data_aquisicao',
        'estado_conservacao',
        'local_armazenamento',
        'observacoes',
        'club_id',
    ];

    protected $casts = [
        'data_aquisicao' => 'date',
        'valor_estimado' => 'decimal:2',
    ];

    public function manutencoes(): HasMany
    {
        return $this->hasMany(PatrimonioManutencao::class)->orderByDesc('data');
    }
}

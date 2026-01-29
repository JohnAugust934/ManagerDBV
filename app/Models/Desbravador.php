<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class Desbravador extends Model
{
    use HasFactory;

    protected $table = 'desbravadores';

    protected $fillable = [
        'ativo',
        'nome',
        'data_nascimento',
        'sexo',
        'unidade_id',
        'classe_atual',
        'email',
        'telefone',
        'endereco',
        'nome_responsavel',
        'telefone_responsavel',
        'numero_sus', // Novo campo
        'tipo_sanguineo',
        'alergias',
        'medicamentos_continuos',
        'plano_saude',
    ];

    protected $casts = [
        'data_nascimento' => 'date',
        'ativo' => 'boolean',
    ];

    public function unidade(): BelongsTo
    {
        return $this->belongsTo(Unidade::class);
    }

    public function especialidades(): BelongsToMany
    {
        return $this->belongsToMany(Especialidade::class, 'desbravador_especialidade')
            ->withPivot('data_conclusao')
            ->withTimestamps();
    }

    public function frequencias(): HasMany
    {
        return $this->hasMany(Frequencia::class);
    }

    public function getTotalPontosAttribute()
    {
        return $this->frequencias->sum('pontos');
    }

    public function scopeAtivos($query)
    {
        return $query->where('ativo', true);
    }

    public function requisitosCumpridos()
    {
        return $this->belongsToMany(Requisito::class, 'desbravador_requisito')
            ->withPivot('data_conclusao', 'user_id')
            ->withTimestamps();
    }

    /**
     * Retorna a % de conclusão da classe atual.
     */
    public function getProgressoClasseAttribute()
    {
        // Busca o objeto Classe baseada no nome salvo em string (ex: "Amigo")
        $classe = Classe::where('nome', $this->classe_atual)->first();

        if (!$classe) return 0;

        $totalRequisitos = $classe->requisitos()->count();
        if ($totalRequisitos == 0) return 0;

        $cumpridos = $this->requisitosCumpridos()
            ->where('classe_id', $classe->id)
            ->count();

        return round(($cumpridos / $totalRequisitos) * 100);
    }
}

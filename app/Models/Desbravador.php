<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use App\Models\Concerns\RegistraAutoria;
use App\Observers\DesbravadorObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[ObservedBy(DesbravadorObserver::class)]
class Desbravador extends Model
{
    use BelongsToTenant, HasFactory, RegistraAutoria;

    protected $table = 'desbravadores';

    protected $fillable = [
        'ativo',
        'nome',
        'data_nascimento',
        'sexo',
        'cpf',
        'cpf_hash',
        'rg',
        'unidade_id',
        'club_id',
        'classe_atual',
        'email',
        'telefone',
        'endereco',
        'nome_responsavel',
        'telefone_responsavel',
        'numero_sus',
        'tipo_sanguineo',
        'alergias',
        'medicamentos_continuos',
        'plano_saude',
        'foto',
        'consentimento_lgpd',
        'consentimento_lgpd_em',
        'consentimento_lgpd_responsavel',
        'usa_imagem_autorizado',
    ];

    protected $casts = [
        'data_nascimento' => 'date',
        'ativo' => 'boolean',
        'consentimento_lgpd' => 'boolean',
        'consentimento_lgpd_em' => 'datetime',
        'usa_imagem_autorizado' => 'boolean',
        // CPF usa mutator/accessor manuais (precisa gerar cpf_hash antes de cifrar).
        // Os demais campos sensíveis usam o cast 'encrypted' do Laravel.
        'rg' => 'encrypted',
        'numero_sus' => 'encrypted',
        'alergias' => 'encrypted',
        'medicamentos_continuos' => 'encrypted',
        'plano_saude' => 'encrypted',
    ];

    /**
     * Grava o CPF criptografado e atualiza cpf_hash (SHA-256 dos dígitos).
     * cpf_hash é a coluna usada pela unique constraint (club_id, cpf_hash),
     * pois o cast 'encrypted' é não-determinístico.
     */
    public function setCpfAttribute(?string $value): void
    {
        if ($value !== null) {
            $this->attributes['cpf_hash'] = hash('sha256', preg_replace('/\D/', '', $value));
            $this->attributes['cpf'] = encrypt($value);
        } else {
            $this->attributes['cpf_hash'] = null;
            $this->attributes['cpf'] = null;
        }
    }

    /**
     * Descriptografa o CPF ao ler. Retorna plaintext como fallback seguro para
     * linhas inseridas diretamente via SQL (testes de migração, seeds legados).
     */
    public function getCpfAttribute(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }
        try {
            return decrypt($value);
        } catch (\Illuminate\Contracts\Encryption\DecryptException) {
            return $value;
        }
    }

    public function unidade(): BelongsTo
    {
        return $this->belongsTo(Unidade::class);
    }

    /**
     * Sem contexto de clube ativo (seeders/console), o clube do desbravador é
     * definido pela sua unidade. Usado pelo trait BelongsToTenant ao criar.
     */
    public function resolveClubIdFromParent(): ?int
    {
        if (! $this->unidade_id) {
            return null;
        }

        return Unidade::withoutGlobalScopes()->whereKey($this->unidade_id)->value('club_id');
    }

    public function classe(): BelongsTo
    {
        return $this->belongsTo(Classe::class, 'classe_atual');
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

    public function getFotoUrlAttribute(): ?string
    {
        if ($this->foto) {
            return asset('storage/'.$this->foto);
        }

        return null;
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
            ->withPivot('user_id', 'data_conclusao')
            ->withTimestamps();
    }

    public function completouRequisito($requisitoId)
    {
        return $this->requisitosCumpridos()->where('requisito_id', $requisitoId)->exists();
    }

    public function getProgressoClasseAttribute()
    {
        if (! $this->classe) {
            return 0;
        }

        $totalRequisitos = $this->classe->requisitos()->count();

        if ($totalRequisitos == 0) {
            return 0;
        }

        $cumpridos = $this->requisitosCumpridos()
            ->where('classe_id', $this->classe->id)
            ->count();

        return round(($cumpridos / $totalRequisitos) * 100);
    }

    public function eventos()
    {
        return $this->belongsToMany(Evento::class, 'desbravador_evento')
            ->withPivot('pago', 'autorizacao_entregue')
            ->withTimestamps();
    }

    public function mensalidades(): HasMany
    {
        return $this->hasMany(Mensalidade::class);
    }
}

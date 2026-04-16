<?php

namespace App\Models;

use App\Support\EspecialidadesCatalog;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class Especialidade extends Model
{
    use HasFactory;

    protected $fillable = [
        'nome',
        'area',
        'codigo',
        'nome_search',
        'area_search',
        'url_oficial',
        'is_oficial',
        'is_avancada',
        'cor_fundo',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'is_oficial' => 'boolean',
        'is_avancada' => 'boolean',
    ];

    /**
     * Relacionamento com Desbravadores (Muitos para Muitos)
     */
    public function desbravadores(): BelongsToMany
    {
        // O segundo argumento é o nome da tabela pivô criada na migração
        return $this->belongsToMany(Desbravador::class, 'desbravador_especialidade');
    }

    /**
     * Relacionamento com Requisitos (Um para Muitos)
     */
    public function requisitos(): HasMany
    {
        return $this->hasMany(Requisito::class);
    }

    public function requisitosOficiais(): HasMany
    {
        return $this->hasMany(EspecialidadeRequisito::class)->orderBy('ordem');
    }

    protected static function booted(): void
    {
        static::saving(function (Especialidade $especialidade): void {
            $especialidade->nome_search = EspecialidadesCatalog::normalizeForSearch($especialidade->nome);
            $especialidade->area_search = EspecialidadesCatalog::normalizeForSearch($especialidade->area);
            $especialidade->is_avancada = EspecialidadesCatalog::isAdvanced($especialidade->nome);

            if (blank($especialidade->cor_fundo)) {
                $especialidade->cor_fundo = EspecialidadesCatalog::colorByArea($especialidade->area);
            }
        });

        static::saved(function (): void {
            self::bumpListCacheVersion();
        });

        static::deleted(function (): void {
            self::bumpListCacheVersion();
        });

        static::created(function (Especialidade $especialidade): void {
            self::registerAudit('created', $especialidade);
        });

        static::updated(function (Especialidade $especialidade): void {
            self::registerAudit('updated', $especialidade);
        });

        static::deleted(function (Especialidade $especialidade): void {
            self::registerAudit('deleted', $especialidade);
        });
    }

    public static function bumpListCacheVersion(): void
    {
        if (! Cache::has('especialidades:index:version')) {
            Cache::forever('especialidades:index:version', 1);
        }

        Cache::increment('especialidades:index:version');
    }

    private static function registerAudit(string $acao, Especialidade $especialidade): void
    {
        if (! Schema::hasTable('especialidade_auditorias')) {
            return;
        }

        DB::table('especialidade_auditorias')->insert([
            'especialidade_id' => $especialidade->id,
            'user_id' => Auth::id(),
            'acao' => $acao,
            'dados' => json_encode([
                'id' => $especialidade->id,
                'nome' => $especialidade->nome,
                'area' => $especialidade->area,
                'codigo' => $especialidade->codigo,
                'is_oficial' => $especialidade->is_oficial,
                'is_avancada' => $especialidade->is_avancada,
            ], JSON_UNESCAPED_UNICODE),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}

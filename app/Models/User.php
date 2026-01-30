<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'club_id',
        'role',              // master, diretor, secretario, tesoureiro, conselheiro, instrutor
        'extra_permissions', // array json
        'is_master',         // mantido para compatibilidade, mas o foco agora é 'role'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_master' => 'boolean',
        'extra_permissions' => 'array', // Converte JSON para Array automaticamente
    ];

    // Constantes de Permissões Disponíveis (Módulos)
    const PERMISSOES = [
        'financeiro' => 'Acesso ao Caixa, Mensalidades e Patrimônio',
        'secretaria' => 'Acesso a Desbravadores, Atas e Atos',
        'unidades' => 'Gestão de Unidades',
        'pedagogico' => 'Classes e Especialidades',
        'eventos' => 'Gestão de Eventos',
    ];

    public function club()
    {
        return $this->belongsTo(Club::class);
    }

    // --- Lógica de Acesso ---

    /**
     * Verifica se o usuário tem permissão para um módulo.
     * Regra: Master tem tudo. Outros dependem do cargo OU permissão extra.
     */
    public function temPermissao(string $modulo): bool
    {
        if ($this->role === 'master') return true;

        // Verifica permissões padrão do cargo
        $permissoesPadrao = $this->getPermissoesPadrao();
        if (in_array($modulo, $permissoesPadrao)) return true;

        // Verifica permissões extras (checkboxes)
        $extras = $this->extra_permissions ?? [];
        return in_array($modulo, $extras);
    }

    /**
     * Define o que cada cargo pode fazer por padrão.
     */
    private function getPermissoesPadrao(): array
    {
        switch ($this->role) {
            case 'diretor':
                return ['financeiro', 'secretaria', 'unidades', 'pedagogico', 'eventos'];

            case 'secretario':
                return ['secretaria', 'unidades', 'pedagogico', 'eventos'];

            case 'tesoureiro':
                return ['financeiro', 'eventos']; // Tesoureiro vê eventos pois tem custo

            case 'conselheiro':
            case 'instrutor':
                return ['pedagogico', 'eventos']; // Apenas pedagógico e ver eventos
                // OBS: Conselheiro vê a própria unidade via lógica no Controller, não permissão global de 'gerir unidades'

            default:
                return [];
        }
    }

    public function isMaster(): bool
    {
        return $this->role === 'master';
    }
}

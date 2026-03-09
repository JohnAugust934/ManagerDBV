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
        'relatorios' => 'Acesso aos Relatórios do Clube', // <-- MÓDULO RECRIADO AQUI
    ];

    public function club()
    {
        return $this->belongsTo(Club::class);
    }

    // --- Lógica de Acesso ---

    public function temPermissao(string $modulo): bool
    {
        if ($this->role === 'master') {
            return true;
        }

        // Verifica permissões padrão do cargo
        $permissoesPadrao = $this->getPermissoesPadrao();
        if (in_array($modulo, $permissoesPadrao)) {
            return true;
        }

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
                return ['financeiro', 'secretaria', 'unidades', 'pedagogico', 'eventos', 'relatorios']; // <-- ADICIONADO AQUI

            case 'secretario':
                return ['secretaria', 'unidades', 'pedagogico', 'eventos', 'relatorios']; // <-- ADICIONADO AQUI

            case 'tesoureiro':
                return ['financeiro', 'eventos', 'relatorios']; // <-- ADICIONADO AQUI

            case 'conselheiro':
            case 'instrutor':
                return ['pedagogico']; // Apenas pedagógico (Eventos foram removidos anteriormente)

            default:
                return [];
        }
    }

    public function isMaster(): bool
    {
        return $this->role === 'master';
    }
}

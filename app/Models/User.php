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
        'is_master',         // mantido para compatibilidade, mas o foco agora e 'role'
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

    // Constantes de permissoes disponiveis (modulos)
    const PERMISSOES = [
        'gestao_acessos' => 'Gestao de Acessos (usuarios e convites)',
        'financeiro' => 'Acesso ao Caixa, Mensalidades e Patrimonio',
        'secretaria' => 'Acesso a Desbravadores, Atas e Atos',
        'unidades' => 'Gestao de Unidades',
        'pedagogico' => 'Classes e Especialidades',
        'eventos' => 'Gestao de Eventos',
        'relatorios' => 'Acesso aos Relatorios do Clube',
    ];

    public function club()
    {
        return $this->belongsTo(Club::class);
    }

    // --- Logica de acesso ---

    public function temPermissao(string $modulo): bool
    {
        if ($this->role === 'master') {
            return true;
        }

        // Verifica permissoes padrao do cargo
        $permissoesPadrao = $this->getPermissoesPadrao();
        if (in_array($modulo, $permissoesPadrao, true)) {
            return true;
        }

        // Verifica permissoes extras (checkboxes)
        $extras = $this->extra_permissions ?? [];

        return in_array($modulo, $extras, true);
    }

    /**
     * Define o que cada cargo pode fazer por padrao.
     */
    private function getPermissoesPadrao(): array
    {
        return match ($this->role) {
            'diretor' => ['financeiro', 'secretaria', 'unidades', 'pedagogico', 'eventos', 'relatorios'],
            'secretario' => ['secretaria', 'unidades', 'pedagogico', 'eventos', 'relatorios'],
            'tesoureiro' => ['financeiro', 'eventos', 'relatorios'],
            'conselheiro', 'instrutor' => ['pedagogico'],
            default => [],
        };
    }

    public function isMaster(): bool
    {
        return $this->role === 'master';
    }
}

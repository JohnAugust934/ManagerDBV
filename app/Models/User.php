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
        'is_platform_admin', // super admin de plataforma (cross-tenant)
        'termos_aceitos_em',
        'passkey_banner_dispensado_em',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_master' => 'boolean',
        'is_platform_admin' => 'boolean',
        'extra_permissions' => 'array', // Converte JSON para Array automaticamente
        'termos_aceitos_em' => 'datetime',
        'passkey_banner_dispensado_em' => 'datetime',
    ];

    // Rotulos amigaveis dos cargos hierarquicos (incl. o cargo de plataforma,
    // que e cross-tenant e distinto do "master" dono de um clube).
    const ROLES_LABEL = [
        'platform_admin' => 'Admin da Plataforma',
        'master' => 'Master',
        'diretor' => 'Diretor',
        'secretario' => 'Secretário',
        'tesoureiro' => 'Tesoureiro',
        'conselheiro' => 'Conselheiro',
        'instrutor' => 'Instrutor',
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

    // --- Passkeys (WebAuthn) ---

    /**
     * Indica se o usuário já possui ao menos uma passkey cadastrada.
     */
    public function temPasskey(): bool
    {
        return \LaravelWebauthn\Services\Webauthn::model()::where('user_id', $this->getAuthIdentifier())
            ->exists();
    }

    /**
     * Decide se o convite (banner) para cadastrar passkey deve aparecer: só para
     * quem ainda não dispensou e ainda não tem nenhuma passkey.
     */
    public function deveVerBannerPasskey(): bool
    {
        return $this->passkey_banner_dispensado_em === null && ! $this->temPasskey();
    }

    // --- Logica de acesso ---

    public function temPermissao(string $modulo): bool
    {
        // Admin de plataforma e master de clube tem acesso total aos modulos.
        if ($this->is_platform_admin || $this->role === 'master') {
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

    public function isPlatformAdmin(): bool
    {
        return $this->is_platform_admin === true;
    }

    /**
     * Pode gerenciar usuarios/convites de cargo "master": o master do clube ou,
     * em modo suporte, o admin da plataforma.
     */
    public function podeGerenciarMasters(): bool
    {
        return $this->isMaster() || $this->isPlatformAdmin();
    }

    /**
     * Rotulo amigavel do cargo, usado na UI. O admin de plataforma tem rotulo
     * proprio mesmo que o role bruto eventualmente difira.
     */
    public function papelLabel(): string
    {
        if ($this->is_platform_admin) {
            return self::ROLES_LABEL['platform_admin'];
        }

        return self::ROLES_LABEL[$this->role] ?? ucfirst((string) $this->role);
    }
}

<?php

namespace Tests\Feature\MultiTenant;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Fase 6 — baixa/suspensão de clube. Um clube desativado (is_active = false) não
 * deixa nenhum usuário vinculado entrar; só o platform admin pode alternar isso,
 * e ele próprio nunca é bloqueado (precisa reativar/dar suporte).
 */
class ClubDeactivationTest extends TestCase
{
    use RefreshDatabase;

    public function test_usuario_de_clube_ativo_acessa_normalmente(): void
    {
        ['master' => $master] = criarClubeComDados('Clube A');

        $this->actingAs($master)->get(route('dashboard'))->assertOk();
    }

    public function test_usuario_de_clube_desativado_e_bloqueado(): void
    {
        ['club' => $club, 'master' => $master] = criarClubeComDados('Clube A');
        $club->update(['is_active' => false]);

        $this->actingAs($master)->get(route('dashboard'))
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_platform_admin_desativa_e_reativa_clube(): void
    {
        $admin = User::factory()->platformAdmin()->create(['club_id' => null]);
        ['club' => $club] = criarClubeComDados('Clube A');

        $this->actingAs($admin)->post(route('platform.toggle-active', $club))
            ->assertRedirect(route('platform.index'));
        $this->assertFalse($club->fresh()->is_active);

        $this->actingAs($admin)->post(route('platform.toggle-active', $club));
        $this->assertTrue($club->fresh()->is_active);
    }

    public function test_usuario_comum_nao_pode_desativar_clube(): void
    {
        ['club' => $club, 'master' => $master] = criarClubeComDados('Clube A');

        $this->actingAs($master)->post(route('platform.toggle-active', $club))->assertForbidden();
        $this->assertTrue($club->fresh()->is_active);
    }

    public function test_platform_admin_acessa_clube_desativado_em_modo_suporte(): void
    {
        $admin = User::factory()->platformAdmin()->create(['club_id' => null]);
        ['club' => $club] = criarClubeComDados('Clube A');
        $club->update(['is_active' => false]);

        $this->actingAs($admin);
        $this->post(route('platform.enter', $club)); // entra em modo suporte

        // O admin (club_id null) não é bloqueado e pode gerir o clube desativado.
        $this->get(route('dashboard'))->assertOk();
    }
}

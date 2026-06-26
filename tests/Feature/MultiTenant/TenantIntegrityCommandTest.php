<?php

namespace Tests\Feature\MultiTenant;

use App\Models\Caixa;
use App\Models\Desbravador;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Cobre o comando `tenant:check-integrity` (gate da Fase 0). Os cenários de
 * corrupção são forjados via DB direto para furar scopes/FK e simular um banco
 * inconsistente — exatamente o que o comando precisa pegar.
 */
class TenantIntegrityCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_passa_quando_os_dados_estao_consistentes(): void
    {
        ['club' => $club] = criarClubeComDados('Clube OK');
        $unidade = \App\Models\Unidade::where('club_id', $club->id)->first();

        Caixa::factory()->forClube($club->id)->create();
        Desbravador::factory()->create(['unidade_id' => $unidade->id]);

        $this->artisan('tenant:check-integrity')
            ->expectsOutputToContain('Integridade multi-tenant OK')
            ->assertExitCode(0);
    }

    public function test_invitation_com_club_id_nulo_e_aceita(): void
    {
        // club_id nulo em tabela estrita virou impossível (NOT NULL na Fase 2).
        // Mas invitations é OPCIONAL: o convite de bootstrap não tem clube.
        criarClubeComDados('Clube A');
        \App\Models\Invitation::create([
            'email' => 'bootstrap@clube.com',
            'token' => 'tok-boot',
            'role' => 'diretor',
        ]);

        $this->artisan('tenant:check-integrity')
            ->expectsOutputToContain('Integridade multi-tenant OK')
            ->assertExitCode(0);
    }

    public function test_detecta_club_id_pendente_para_clube_inexistente(): void
    {
        ['club' => $club] = criarClubeComDados('Clube A');
        $caixa = Caixa::factory()->forClube($club->id)->create();

        DB::table('caixas')->where('id', $caixa->id)->update(['club_id' => 999999]);

        $this->artisan('tenant:check-integrity')->assertExitCode(1);
    }

    public function test_detecta_usuario_comum_sem_clube(): void
    {
        // Sem nenhum clube/usuário consistente; cria um usuário órfão não-admin.
        User::factory()->create(['club_id' => null, 'is_platform_admin' => false]);

        $this->artisan('tenant:check-integrity')->assertExitCode(1);
    }

    public function test_platform_admin_sem_clube_nao_e_problema(): void
    {
        $club = \App\Models\Club::create(['nome' => 'Clube A', 'cidade' => 'SP']);
        User::factory()->create(['club_id' => $club->id, 'role' => 'master']);
        User::factory()->create(['club_id' => null, 'is_platform_admin' => true]);

        $this->artisan('tenant:check-integrity')->assertExitCode(0);
    }

    public function test_detecta_desbravador_sem_unidade(): void
    {
        ['unidade' => $unidade] = criarClubeComDados('Clube A');
        $dbv = Desbravador::factory()->create(['unidade_id' => $unidade->id]);

        // Remove a unidade mantendo club_id (dado inconsistente).
        DB::table('desbravadores')->where('id', $dbv->id)->update(['unidade_id' => null]);

        $this->artisan('tenant:check-integrity')->assertExitCode(1);
    }

    public function test_saida_json_estrutura_o_resultado(): void
    {
        ['club' => $club] = criarClubeComDados('Clube A');
        $caixa = Caixa::factory()->forClube($club->id)->create();
        // club_id pendente (aponta para clube inexistente) — reproduzível no SQLite,
        // onde a FK é pulada; o NOT NULL impede forjar nulo.
        DB::table('caixas')->where('id', $caixa->id)->update(['club_id' => 999999]);

        $this->artisan('tenant:check-integrity --json')
            ->expectsOutputToContain('"ok": false')
            ->assertExitCode(1);
    }
}

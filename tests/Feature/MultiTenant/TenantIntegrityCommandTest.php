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

    public function test_detecta_club_id_nulo_em_tabela_de_tenant(): void
    {
        ['club' => $club] = criarClubeComDados('Clube A');
        $caixa = Caixa::factory()->forClube($club->id)->create();

        // Simula linha órfã (invisível sob fail-closed).
        DB::table('caixas')->where('id', $caixa->id)->update(['club_id' => null]);

        $this->artisan('tenant:check-integrity')
            ->expectsOutputToContain('Problemas de integridade')
            ->assertExitCode(1);
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
        ['club' => $club] = criarClubeComDados('Clube A');
        Desbravador::factory()->create(['unidade_id' => null]);

        $this->artisan('tenant:check-integrity')->assertExitCode(1);
    }

    public function test_saida_json_estrutura_o_resultado(): void
    {
        ['club' => $club] = criarClubeComDados('Clube A');
        $caixa = Caixa::factory()->forClube($club->id)->create();
        DB::table('caixas')->where('id', $caixa->id)->update(['club_id' => null]);

        $this->artisan('tenant:check-integrity --json')
            ->expectsOutputToContain('"ok": false')
            ->assertExitCode(1);
    }
}

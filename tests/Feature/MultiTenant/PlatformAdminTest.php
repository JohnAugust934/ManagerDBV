<?php

namespace Tests\Feature\MultiTenant;

use App\Models\Caixa;
use App\Models\Club;
use App\Models\User;
use App\Services\ClubContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PlatformAdminTest extends TestCase
{
    use RefreshDatabase;

    private Club $clubA;

    private Club $clubB;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->clubA = Club::create(['nome' => 'Clube A', 'cidade' => 'SP']);
        $this->clubB = Club::create(['nome' => 'Clube B', 'cidade' => 'RJ']);

        Caixa::create(['descricao' => 'Caixa A', 'valor' => 10, 'tipo' => 'entrada', 'data_movimentacao' => now(), 'categoria' => 'x', 'club_id' => $this->clubA->id]);
        Caixa::create(['descricao' => 'Caixa B', 'valor' => 20, 'tipo' => 'entrada', 'data_movimentacao' => now(), 'categoria' => 'x', 'club_id' => $this->clubB->id]);

        $this->admin = User::factory()->platformAdmin()->create();
    }

    public function test_platform_admin_acessa_o_painel(): void
    {
        $this->actingAs($this->admin)->get(route('platform.index'))->assertOk();
    }

    public function test_usuario_comum_nao_acessa_o_painel(): void
    {
        $user = User::factory()->create(['club_id' => $this->clubA->id, 'role' => 'master']);

        $this->actingAs($user)->get(route('platform.index'))->assertForbidden();
    }

    public function test_enter_club_salva_sessao_e_redireciona(): void
    {
        $this->actingAs($this->admin)
            ->post(route('platform.enter', $this->clubB))
            ->assertRedirect(route('dashboard'));

        $this->assertEquals($this->clubB->id, session(ClubContext::SESSION_KEY));
    }

    public function test_exit_club_limpa_sessao_e_redireciona(): void
    {
        $this->actingAs($this->admin);
        session([ClubContext::SESSION_KEY => $this->clubB->id]);

        $this->post(route('platform.exit'))->assertRedirect(route('platform.index'));

        $this->assertNull(session(ClubContext::SESSION_KEY));
    }

    public function test_apos_enter_as_queries_sao_filtradas_pelo_clube(): void
    {
        $this->actingAs($this->admin);
        $this->post(route('platform.enter', $this->clubA));

        $this->assertSame(['Caixa A'], Caixa::pluck('descricao')->all());
    }

    public function test_apos_exit_as_queries_voltam_sem_filtro(): void
    {
        $this->actingAs($this->admin);
        session([ClubContext::SESSION_KEY => $this->clubA->id]);
        $this->post(route('platform.exit'));

        $this->assertEqualsCanonicalizing(['Caixa A', 'Caixa B'], Caixa::pluck('descricao')->all());
    }

    public function test_platform_admin_sem_contexto_e_redirecionado_das_telas_de_clube(): void
    {
        $this->actingAs($this->admin);

        // Telas escopadas por clube → volta para o painel (evita ver dados mesclados).
        $this->get(route('dashboard'))->assertRedirect(route('platform.index'));
        $this->get(route('caixa.index'))->assertRedirect(route('platform.index'));
    }

    public function test_platform_admin_sem_contexto_acessa_painel_e_backups(): void
    {
        \Illuminate\Support\Facades\Storage::fake('local');
        \Illuminate\Support\Facades\Storage::fake('r2');

        $this->actingAs($this->admin);

        $this->get(route('platform.index'))->assertOk();
        $this->get(route('backups.index'))->assertOk();
        $this->get(route('profile.edit'))->assertOk();
    }

    public function test_platform_admin_em_modo_suporte_acessa_telas_de_clube(): void
    {
        $this->actingAs($this->admin);
        $this->post(route('platform.enter', $this->clubA));

        $this->get(route('dashboard'))->assertOk();
        $this->get(route('caixa.index'))->assertOk();
    }

    public function test_platform_admin_cria_clube_com_master_inicial(): void
    {
        $this->actingAs($this->admin)->get(route('platform.clubs.create'))->assertOk();

        $this->actingAs($this->admin)->post(route('platform.clubs.store'), [
            'nome' => 'Clube Novo',
            'cidade' => 'BH',
            'associacao' => 'AMC',
            'master_name' => 'Master Novo',
            'master_email' => 'masternovo@teste.com',
            'master_password' => 'password123',
        ])->assertRedirect(route('platform.index'));

        $this->assertDatabaseHas('clubs', ['nome' => 'Clube Novo']);

        $club = Club::where('nome', 'Clube Novo')->first();
        $this->assertDatabaseHas('users', [
            'email' => 'masternovo@teste.com',
            'role' => 'master',
            'club_id' => $club->id,
            'is_platform_admin' => false,
        ]);
    }
}

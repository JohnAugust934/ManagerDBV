<?php

namespace Tests\Feature\MultiTenant;

use App\Models\Ata;
use App\Models\Ato;
use App\Models\AttendanceColumn;
use App\Models\Caixa;
use App\Models\Desbravador;
use App\Models\Evento;
use App\Models\Invitation;
use App\Models\Mensalidade;
use App\Models\Patrimonio;
use App\Models\RankingSnapshot;
use App\Models\Unidade;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Cobertura ampla de isolamento por tenant: usuário de um clube nunca enxerga
 * nem acessa dados de outro clube.
 */
class IsolamentoTenantTest extends TestCase
{
    use RefreshDatabase;

    public function test_listagens_so_mostram_dados_do_proprio_clube(): void
    {
        ['club' => $clubA, 'master' => $masterA, 'unidade' => $unidadeA] = criarClubeComDados('Clube A');
        ['club' => $clubB, 'unidade' => $unidadeB] = criarClubeComDados('Clube B');

        Caixa::factory()->forClube($clubA->id)->create(['descricao' => 'Mov A']);
        Caixa::factory()->forClube($clubB->id)->create(['descricao' => 'Mov B']);
        Evento::factory()->forClube($clubA->id)->create(['nome' => 'Evento A']);
        Evento::factory()->forClube($clubB->id)->create(['nome' => 'Evento B']);
        Patrimonio::factory()->forClube($clubA->id)->create(['item' => 'Item A']);
        Patrimonio::factory()->forClube($clubB->id)->create(['item' => 'Item B']);
        Ata::factory()->forClube($clubA->id)->create(['titulo' => 'Ata A', 'hora_inicio' => '09:00', 'hora_fim' => '10:00', 'local' => 'X']);
        Ata::factory()->forClube($clubB->id)->create(['titulo' => 'Ata B', 'hora_inicio' => '09:00', 'hora_fim' => '10:00', 'local' => 'X']);
        Ato::factory()->forClube($clubA->id)->create(['descricao' => 'Ato A']);
        Ato::factory()->forClube($clubB->id)->create(['descricao' => 'Ato B']);

        $dbvA = Desbravador::factory()->create(['unidade_id' => $unidadeA->id]);
        $dbvB = Desbravador::factory()->create(['unidade_id' => $unidadeB->id]);
        Mensalidade::create(['desbravador_id' => $dbvA->id, 'mes' => 1, 'ano' => 2026, 'valor' => 15, 'status' => 'pendente']);
        Mensalidade::create(['desbravador_id' => $dbvB->id, 'mes' => 1, 'ano' => 2026, 'valor' => 15, 'status' => 'pendente']);

        $this->actingAs($masterA);

        $this->assertSame(1, Caixa::count());
        $this->assertSame(1, Evento::count());
        $this->assertSame(1, Patrimonio::count());
        $this->assertSame(1, Ata::count());
        $this->assertSame(1, Ato::count());
        $this->assertSame(1, Unidade::where('club_id', $clubA->id)->count());
        $this->assertEquals([$dbvA->id], Desbravador::pluck('id')->all());
        $this->assertEquals([$dbvA->id], Mensalidade::pluck('desbravador_id')->all());
    }

    public function test_usuario_nao_acessa_registro_de_outro_clube_via_url(): void
    {
        ['master' => $masterA] = criarClubeComDados('Clube A');
        ['club' => $clubB, 'unidade' => $unidadeB] = criarClubeComDados('Clube B');

        $eventoB = Evento::factory()->forClube($clubB->id)->create();
        $dbvB = Desbravador::factory()->create(['unidade_id' => $unidadeB->id]);

        // Route-model binding aplica o global scope → registro de outro clube some (404).
        $this->actingAs($masterA)->get(route('eventos.edit', $eventoB))->assertNotFound();
        $this->actingAs($masterA)->get(route('desbravadores.show', $dbvB))->assertNotFound();
    }

    public function test_criacao_de_caixa_usa_o_clube_do_usuario_logado(): void
    {
        ['club' => $clubA, 'master' => $masterA] = criarClubeComDados('Clube A');

        $this->actingAs($masterA)->post(route('caixa.store'), [
            'descricao' => 'Doação',
            'tipo' => 'entrada',
            'valor' => 100,
            'data_movimentacao' => now()->toDateString(),
            'categoria' => 'Doações',
        ])->assertRedirect(route('caixa.index'));

        $this->assertDatabaseHas('caixas', ['descricao' => 'Doação', 'club_id' => $clubA->id]);
    }

    public function test_attendance_columns_e_invitations_sao_isolados(): void
    {
        ['club' => $clubA, 'master' => $masterA] = criarClubeComDados('Clube A');
        ['club' => $clubB] = criarClubeComDados('Clube B');

        AttendanceColumn::create(['club_id' => $clubA->id, 'key' => 'presente', 'name' => 'Presente', 'points' => 10, 'is_fixed' => true, 'is_active' => true, 'sort_order' => 1]);
        AttendanceColumn::create(['club_id' => $clubB->id, 'key' => 'presente', 'name' => 'Presente', 'points' => 10, 'is_fixed' => true, 'is_active' => true, 'sort_order' => 1]);

        Invitation::create(['email' => 'a@a.com', 'token' => 'tok-a', 'role' => 'secretario', 'club_id' => $clubA->id]);
        Invitation::create(['email' => 'b@b.com', 'token' => 'tok-b', 'role' => 'secretario', 'club_id' => $clubB->id]);

        $this->actingAs($masterA)->get(route('invites.index'))
            ->assertOk()
            ->assertSee('a@a.com')
            ->assertDontSee('b@b.com');

        $this->assertSame(1, AttendanceColumn::where('club_id', $clubA->id)->count());
    }

    public function test_ranking_snapshots_sao_isolados_por_clube(): void
    {
        ['club' => $clubA] = criarClubeComDados('Clube A');
        ['club' => $clubB] = criarClubeComDados('Clube B');

        RankingSnapshot::create(['year' => 2025, 'scope' => 'unidades', 'club_id' => $clubA->id, 'entries' => [], 'generated_at' => now()]);
        RankingSnapshot::create(['year' => 2025, 'scope' => 'unidades', 'club_id' => $clubB->id, 'entries' => [], 'generated_at' => now()]);

        $this->assertSame(1, RankingSnapshot::where('club_id', $clubA->id)->count());
        $this->assertSame(2, RankingSnapshot::count());
    }
}

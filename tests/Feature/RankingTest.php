<?php

namespace Tests\Feature;

use App\Models\Club;
use App\Models\Desbravador;
use App\Models\Frequencia;
use App\Models\RankingSnapshot;
use App\Models\Unidade;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RankingTest extends TestCase
{
    use RefreshDatabase;

    public function test_usuario_pode_ver_ranking_unidades()
    {
        $clube = Club::create(['nome' => 'Clube Teste', 'cidade' => 'SP']);
        $user = User::factory()->create(['club_id' => $clube->id, 'role' => 'secretario']);

        $response = $this->actingAs($user)->get(route('ranking.unidades'));
        $response->assertStatus(200);
        $response->assertViewHas('titulo', 'Ranking das Unidades');
        $response->assertViewHas('ano', now()->year);
    }

    public function test_usuario_pode_ver_ranking_individual()
    {
        $clube = Club::create(['nome' => 'Clube Teste', 'cidade' => 'SP']);
        $user = User::factory()->create(['club_id' => $clube->id, 'role' => 'secretario']);

        $response = $this->actingAs($user)->get(route('ranking.desbravadores'));
        $response->assertStatus(200);
        $response->assertViewHas('titulo', 'Ranking Individual');
        $response->assertViewHas('ano', now()->year);
    }

    public function test_calculo_ranking_individual_correto()
    {
        $clube = Club::create(['nome' => 'Clube Teste', 'cidade' => 'SP']);
        $user = User::factory()->create(['club_id' => $clube->id, 'role' => 'secretario']);

        $unidade = Unidade::factory()->create();

        // Desbravador 1: Full (30 pts)
        $dbv1 = Desbravador::factory()->create(['unidade_id' => $unidade->id, 'nome' => 'Campeão', 'ativo' => true]);
        Frequencia::create(['desbravador_id' => $dbv1->id, 'data' => now(), 'presente' => true, 'uniforme' => true, 'biblia' => true, 'pontual' => true]);

        // Desbravador 2: Só Presença (10 pts)
        $dbv2 = Desbravador::factory()->create(['unidade_id' => $unidade->id, 'nome' => 'Iniciante', 'ativo' => true]);
        Frequencia::create(['desbravador_id' => $dbv2->id, 'data' => now(), 'presente' => true, 'uniforme' => false, 'biblia' => false, 'pontual' => false]);

        $response = $this->actingAs($user)->get(route('ranking.desbravadores'));

        $dados = $response->viewData('data');

        $this->assertEquals('Campeão', $dados->first()->nome);
        $this->assertEquals(30, $dados->first()->pontos);

        $this->assertEquals('Iniciante', $dados->last()->nome);
        $this->assertEquals(10, $dados->last()->pontos);
    }

    public function test_ranking_considera_apenas_pontos_do_ano_atual()
    {
        $clube = Club::create(['nome' => 'Clube Ano', 'cidade' => 'SP']);
        $user = User::factory()->create(['club_id' => $clube->id, 'role' => 'secretario']);

        $unidade = Unidade::factory()->create();
        $dbv = Desbravador::factory()->create([
            'unidade_id' => $unidade->id,
            'nome' => 'Pontuacao Atual',
            'ativo' => true,
        ]);

        Frequencia::create([
            'desbravador_id' => $dbv->id,
            'data' => now(),
            'presente' => true,
            'uniforme' => true,
            'biblia' => true,
            'pontual' => true,
        ]);

        Frequencia::create([
            'desbravador_id' => $dbv->id,
            'data' => now()->subYear(),
            'presente' => true,
            'uniforme' => true,
            'biblia' => true,
            'pontual' => true,
        ]);

        $response = $this->actingAs($user)->get(route('ranking.desbravadores'));
        $dados = $response->viewData('data');

        $this->assertEquals(30, $dados->first()->pontos);
    }

    public function test_snapshot_anual_do_ranking_pode_ser_gerado_para_auditoria()
    {
        $unidade = Unidade::factory()->create();
        $desbravador = Desbravador::factory()->create([
            'unidade_id' => $unidade->id,
            'ativo' => true,
        ]);

        Frequencia::create([
            'desbravador_id' => $desbravador->id,
            'data' => now()->subYear(),
            'presente' => true,
            'pontual' => true,
            'biblia' => true,
            'uniforme' => true,
        ]);

        $this->artisan('ranking:snapshot '.now()->subYear()->year)
            ->assertExitCode(0);

        $this->assertDatabaseHas('ranking_snapshots', [
            'year' => now()->subYear()->year,
            'scope' => 'unidades',
        ]);

        $snapshot = RankingSnapshot::where('year', now()->subYear()->year)
            ->where('scope', 'desbravadores')
            ->firstOrFail();

        $this->assertNotEmpty($snapshot->entries);
        $this->assertSame($desbravador->nome, $snapshot->entries[0]['name']);
    }
}

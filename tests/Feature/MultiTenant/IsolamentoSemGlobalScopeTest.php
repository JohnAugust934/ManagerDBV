<?php

namespace Tests\Feature\MultiTenant;

use App\Models\Desbravador;
use App\Models\Frequencia;
use App\Models\Unidade;
use App\Services\ClubExportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Verifica que ocorrências de withoutGlobalScopes() têm filtro de club_id explícito
 * e não permitem vazamento cross-tenant.
 */
class IsolamentoSemGlobalScopeTest extends TestCase
{
    use RefreshDatabase;

    public function test_clubexportservice_retorna_apenas_dados_do_clube_solicitado(): void
    {
        ['club' => $clubA] = criarClubeComDados('Clube A');
        ['club' => $clubB] = criarClubeComDados('Clube B');

        $unidadeA = Unidade::factory()->create(['club_id' => $clubA->id]);
        $unidadeB = Unidade::factory()->create(['club_id' => $clubB->id]);

        Desbravador::factory()->create(['unidade_id' => $unidadeA->id, 'nome' => 'Desbravador A']);
        Desbravador::factory()->create(['unidade_id' => $unidadeB->id, 'nome' => 'Desbravador B']);

        $export = app(ClubExportService::class)->export($clubA);

        $nomes = array_column($export['desbravadores'], 'nome');
        $this->assertContains('Desbravador A', $nomes);
        $this->assertNotContains('Desbravador B', $nomes, 'Dados do clube B não devem aparecer na exportação do clube A');
    }

    public function test_registro_de_chamada_nao_aceita_desbravadores_de_outro_clube(): void
    {
        ['club' => $clubA, 'master' => $masterA, 'unidade' => $unidadeA] = criarClubeComDados('Clube A');
        ['unidade' => $unidadeB] = criarClubeComDados('Clube B');

        $dbvA = Desbravador::factory()->create(['unidade_id' => $unidadeA->id]);
        $dbvB = Desbravador::factory()->create(['unidade_id' => $unidadeB->id]);

        $data = now()->toDateString();

        // Tenta registrar chamada incluindo um desbravador do clube B
        $this->actingAs($masterA)->post(route('frequencia.store'), [
            'data' => $data,
            'unidades_submetidas' => [$unidadeA->id],
            'presencas' => [
                $dbvA->id => ['presente' => '1'],
                $dbvB->id => ['presente' => '1'], // clube B — deve ser ignorado
            ],
        ])->assertRedirect();

        // Frequência do desbravador A criada corretamente
        $this->assertDatabaseHas('frequencias', ['desbravador_id' => $dbvA->id]);

        // Frequência do desbravador B NÃO deve ter sido criada
        $this->assertDatabaseMissing('frequencias', ['desbravador_id' => $dbvB->id]);
    }

    public function test_desbravador_resolve_club_id_sem_vazar_para_outro_scope(): void
    {
        ['club' => $clubA] = criarClubeComDados('Clube A');
        ['club' => $clubB] = criarClubeComDados('Clube B');

        $unidadeA = Unidade::factory()->create(['club_id' => $clubA->id]);
        $unidadeB = Unidade::factory()->create(['club_id' => $clubB->id]);

        $dbvA = Desbravador::factory()->create(['unidade_id' => $unidadeA->id]);
        $dbvB = Desbravador::factory()->create(['unidade_id' => $unidadeB->id]);

        // Desbravador::resolveClubId() usa withoutGlobalScopes internamente —
        // deve retornar o club_id correto de cada desbravador sem confusão.
        $this->assertEquals($clubA->id, $dbvA->resolveClubIdFromParent());
        $this->assertEquals($clubB->id, $dbvB->resolveClubIdFromParent());
        $this->assertNotEquals($clubA->id, $dbvB->resolveClubIdFromParent());
    }

    public function test_frequencia_resolve_club_id_corretamente(): void
    {
        ['club' => $clubA] = criarClubeComDados('Clube A');
        ['club' => $clubB] = criarClubeComDados('Clube B');

        $unidadeA = Unidade::factory()->create(['club_id' => $clubA->id]);
        $unidadeB = Unidade::factory()->create(['club_id' => $clubB->id]);

        $dbvA = Desbravador::factory()->create(['unidade_id' => $unidadeA->id]);
        $dbvB = Desbravador::factory()->create(['unidade_id' => $unidadeB->id]);

        $freqA = Frequencia::create(['desbravador_id' => $dbvA->id, 'data' => now(), 'presente' => true]);
        $freqB = Frequencia::create(['desbravador_id' => $dbvB->id, 'data' => now(), 'presente' => true]);

        // Frequencia::resolveClubId() usa Desbravador::withoutGlobalScopes internamente
        $this->assertEquals($clubA->id, $freqA->resolveClubIdFromParent());
        $this->assertEquals($clubB->id, $freqB->resolveClubIdFromParent());
    }
}

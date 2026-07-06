<?php

namespace Tests\Feature\Privacidade;

use App\Models\Desbravador;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TermoPrivacidadePdfTest extends TestCase
{
    use RefreshDatabase;

    public function test_gera_pdf_individual(): void
    {
        ['master' => $master, 'unidade' => $unidade] = criarClubeComDados('Clube A');
        $dbv = Desbravador::factory()->create(['unidade_id' => $unidade->id]);

        $response = $this->actingAs($master)->get(route('relatorios.termo-privacidade', $dbv));

        $response->assertOk();
        $this->assertStringContainsString('application/pdf', $response->headers->get('content-type'));
    }

    public function test_nao_gera_pdf_de_membro_de_outro_clube(): void
    {
        ['master' => $masterA] = criarClubeComDados('Clube A');
        ['unidade' => $unidadeB] = criarClubeComDados('Clube B');

        $dbvB = Desbravador::factory()->create(['unidade_id' => $unidadeB->id]);

        $this->actingAs($masterA)->get(route('relatorios.termo-privacidade', $dbvB))->assertNotFound();
    }

    public function test_lote_respeita_club_scope(): void
    {
        ['master' => $masterA, 'unidade' => $unidadeA] = criarClubeComDados('Clube A');
        ['unidade' => $unidadeB] = criarClubeComDados('Clube B');

        $dbvA = Desbravador::factory()->create(['unidade_id' => $unidadeA->id]);
        $dbvB = Desbravador::factory()->create(['unidade_id' => $unidadeB->id]);

        // IDs de outro clube passados manualmente são ignorados pelo ClubScope.
        $this->actingAs($masterA)
            ->post(route('relatorios.termo-privacidade.lote'), ['desbravador_ids' => [$dbvA->id, $dbvB->id]])
            ->assertOk();

        $this->actingAs($masterA);
        $this->assertEquals([$dbvA->id], Desbravador::whereIn('id', [$dbvA->id, $dbvB->id])->pluck('id')->all());
    }
}

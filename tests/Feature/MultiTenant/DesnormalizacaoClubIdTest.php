<?php

namespace Tests\Feature\MultiTenant;

use App\Models\Desbravador;
use App\Models\Frequencia;
use App\Models\Mensalidade;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Fase 1 — desnormalização de club_id em desbravadores/frequencias/mensalidades.
 * Garante que o club_id é preenchido automaticamente, que o isolamento agora é
 * direto (e que Frequencia, antes sem scope, parou de vazar entre clubes).
 */
class DesnormalizacaoClubIdTest extends TestCase
{
    use RefreshDatabase;

    public function test_desbravador_recebe_club_id_da_unidade_sem_contexto(): void
    {
        ['club' => $club, 'unidade' => $unidade] = criarClubeComDados('Clube A');

        // Sem actingAs: o trait deriva o clube da unidade.
        $dbv = Desbravador::factory()->create(['unidade_id' => $unidade->id]);

        $this->assertSame($club->id, $dbv->club_id);
    }

    public function test_frequencia_e_mensalidade_herdam_club_id_do_desbravador(): void
    {
        ['club' => $club, 'unidade' => $unidade] = criarClubeComDados('Clube A');
        $dbv = Desbravador::factory()->create(['unidade_id' => $unidade->id]);

        $freq = Frequencia::create(['desbravador_id' => $dbv->id, 'data' => now()]);
        $mens = Mensalidade::create(['desbravador_id' => $dbv->id, 'mes' => 1, 'ano' => 2026, 'valor' => 15, 'status' => 'pendente']);

        $this->assertSame($club->id, $freq->club_id);
        $this->assertSame($club->id, $mens->club_id);
    }

    public function test_club_id_do_contexto_tem_precedencia_ao_criar(): void
    {
        ['club' => $club, 'master' => $master, 'unidade' => $unidade] = criarClubeComDados('Clube A');
        $dbv = Desbravador::factory()->create(['unidade_id' => $unidade->id]);

        $this->actingAs($master);
        $freq = Frequencia::create(['desbravador_id' => $dbv->id, 'data' => now()]);

        $this->assertSame($club->id, $freq->club_id);
    }

    public function test_frequencia_agora_e_isolada_por_clube(): void
    {
        ['club' => $clubA, 'master' => $masterA, 'unidade' => $unidadeA] = criarClubeComDados('Clube A');
        ['unidade' => $unidadeB] = criarClubeComDados('Clube B');

        $dbvA = Desbravador::factory()->create(['unidade_id' => $unidadeA->id]);
        $dbvB = Desbravador::factory()->create(['unidade_id' => $unidadeB->id]);

        Frequencia::create(['desbravador_id' => $dbvA->id, 'data' => now()]);
        Frequencia::create(['desbravador_id' => $dbvB->id, 'data' => now()]);

        // Antes da Fase 1, Frequencia não tinha scope → contava as duas (vazamento).
        $this->actingAs($masterA);
        $this->assertSame(1, Frequencia::count());
        $this->assertEquals([$dbvA->id], Frequencia::pluck('desbravador_id')->all());
    }

    public function test_integridade_detecta_divergencia_entre_desbravador_e_unidade(): void
    {
        ['club' => $clubA, 'unidade' => $unidadeA] = criarClubeComDados('Clube A');
        ['club' => $clubB] = criarClubeComDados('Clube B');

        $dbv = Desbravador::factory()->create(['unidade_id' => $unidadeA->id]);

        // Força o club_id do desbravador a divergir do clube da sua unidade.
        DB::table('desbravadores')->where('id', $dbv->id)->update(['club_id' => $clubB->id]);

        $this->artisan('tenant:check-integrity')->assertExitCode(1);
    }
}

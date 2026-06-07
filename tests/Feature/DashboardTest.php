<?php

namespace Tests\Feature;

use App\Models\Caixa;
use App\Models\Classe;
use App\Models\Club;
use App\Models\Desbravador;
use App\Models\Frequencia;
use App\Models\Mensalidade;
use App\Models\Unidade;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_exibe_estatisticas_corretas_com_saldo_positivo()
    {
        $clube = Club::create(['nome' => 'Clube Teste', 'cidade' => 'SP']);
        $user = User::factory()->create(['club_id' => $clube->id, 'role' => 'diretor']);

        // GlobalScope ClubScope aplica filtro — registros devem ter club_id correto.
        Caixa::factory()->create(['tipo' => 'entrada', 'valor' => 150.00, 'club_id' => $clube->id]);
        Caixa::factory()->create(['tipo' => 'saida', 'valor' => 50.00, 'club_id' => $clube->id]);

        // Desbravadores devem pertencer ao clube (GlobalScope DesbravadorClubScope).
        $unidade = Unidade::factory()->create(['club_id' => $clube->id]);
        $classe = Classe::factory()->create();

        $dbv1 = Desbravador::factory()->create(['ativo' => true, 'unidade_id' => $unidade->id, 'classe_atual' => $classe->id]);
        $dbv2 = Desbravador::factory()->create(['ativo' => true, 'unidade_id' => $unidade->id, 'classe_atual' => $classe->id]);

        Mensalidade::create(['desbravador_id' => $dbv1->id, 'mes' => now()->month, 'ano' => now()->year, 'valor' => 10, 'status' => 'pago', 'data_pagamento' => now()]);
        Mensalidade::create(['desbravador_id' => $dbv2->id, 'mes' => now()->month, 'ano' => now()->year, 'valor' => 10, 'status' => 'pendente']);

        Frequencia::create(['desbravador_id' => $dbv1->id, 'data' => now()->subDays(7), 'presente' => true]);
        Frequencia::create(['desbravador_id' => $dbv2->id, 'data' => now()->subDays(7), 'presente' => false]);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertViewHas('saldoAtual', 100.00);
        $response->assertViewHas('taxaInadimplencia', 50.0);
        $response->assertViewHas('totalAtivos', 2);

        $dadosGrafico = $response->viewData('dadosGrafico');
        $this->assertNotEmpty($dadosGrafico);
    }

    public function test_dashboard_exibe_corretamente_saldo_negativo()
    {
        $clube = Club::create(['nome' => 'Clube Teste', 'cidade' => 'SP']);
        $user = User::factory()->create(['club_id' => $clube->id, 'role' => 'diretor']);

        Caixa::factory()->create(['tipo' => 'entrada', 'valor' => 50.00, 'club_id' => $clube->id]);
        Caixa::factory()->create(['tipo' => 'saida', 'valor' => 100.00, 'club_id' => $clube->id]);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertViewHas('saldoAtual', -50.00);
    }
}

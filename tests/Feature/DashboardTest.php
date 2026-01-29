<?php

namespace Tests\Feature;

use App\Models\Caixa;
use App\Models\Club;
use App\Models\Desbravador;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Carbon\Carbon;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_exibe_estatisticas_corretas()
    {
        // 1. Setup
        $clube = Club::create(['nome' => 'Clube Teste', 'cidade' => 'SP']);
        $user = User::factory()->create(['club_id' => $clube->id]);

        // 2. Dados
        Desbravador::factory()->count(5)->create(); // 5 Membros

        // CORREÇÃO: Adicionado 'descricao' que é obrigatório
        Caixa::create([
            'descricao' => 'Entrada Teste',
            'tipo' => 'entrada',
            'valor' => 100,
            'data_movimentacao' => now()
        ]);

        Caixa::create([
            'descricao' => 'Saída Teste',
            'tipo' => 'saida',
            'valor' => 30,
            'data_movimentacao' => now()
        ]);
        // Saldo esperado = 70

        $aniversariante = Desbravador::factory()->create(['data_nascimento' => Carbon::now()->startOfMonth()]);
        // Total membros = 6

        // 3. Ação
        $response = $this->actingAs($user)->get(route('dashboard'));

        // 4. Asserts
        $response->assertStatus(200);

        // Verifica se os dados chegaram na view
        $response->assertViewHas('totalMembros', 6);
        $response->assertViewHas('saldoAtual', 70);
        $response->assertViewHas('aniversariantes');

        // Verifica se o aniversariante está na lista
        $listaAniversariantes = $response->viewData('aniversariantes');
        $this->assertTrue($listaAniversariantes->contains($aniversariante));
    }
}

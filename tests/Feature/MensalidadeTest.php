<?php

namespace Tests\Feature;

use App\Models\Caixa;
use App\Models\Club;
use App\Models\Desbravador;
use App\Models\Mensalidade;
use App\Models\Unidade;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MensalidadeTest extends TestCase
{
    use RefreshDatabase;

    public function test_pode_gerar_mensalidades_para_todos_os_ativos()
    {
        $clube = Club::create(['nome' => 'Clube', 'cidade' => 'SP']);
        $user = User::factory()->create(['club_id' => $clube->id, 'role' => 'tesoureiro']);
        $unidade = Unidade::factory()->create();
        Desbravador::factory()->count(3)->create(['unidade_id' => $unidade->id, 'ativo' => true]);

        $response = $this->actingAs($user)->post(route('mensalidades.gerar'), [
            'mes' => 10,
            'ano' => 2026,
            'valor' => 15.00
        ]);

        $response->assertRedirect();
        $this->assertDatabaseCount('mensalidades', 3);
    }

    public function test_pagar_mensalidade_muda_status_e_lanca_no_caixa()
    {
        $clube = Club::create(['nome' => 'Clube', 'cidade' => 'SP']);
        $user = User::factory()->create(['club_id' => $clube->id, 'role' => 'tesoureiro']);
        $unidade = Unidade::factory()->create();
        $dbv = Desbravador::factory()->create(['unidade_id' => $unidade->id]);

        $mensalidade = Mensalidade::create([
            'desbravador_id' => $dbv->id,
            'mes' => 10,
            'ano' => 2026,
            'valor' => 15.00,
            'status' => 'pendente'
        ]);

        $response = $this->actingAs($user)->post(route('mensalidades.pagar', $mensalidade->id));

        $response->assertRedirect();

        $mensalidade->refresh();
        $this->assertEquals('pago', $mensalidade->status);

        $this->assertDatabaseHas('caixas', [
            'tipo' => 'entrada',
            'valor' => 15.00
        ]);
    }

    public function test_scope_inadimplentes_filtra_atrasados()
    {
        $unidade = Unidade::factory()->create();
        $dbv1 = Desbravador::factory()->create(['unidade_id' => $unidade->id]);
        $dbv2 = Desbravador::factory()->create(['unidade_id' => $unidade->id]);

        // Caso 1: Pago (OK)
        Mensalidade::create([
            'desbravador_id' => $dbv1->id,
            'status' => 'pago',
            'valor' => 10,
            'mes' => now()->month,
            'ano' => now()->year
        ]);

        // Caso 2: Pendente (ATRASADO) - Usar ano passado para garantir
        Mensalidade::create([
            'desbravador_id' => $dbv2->id,
            'status' => 'pendente',
            'valor' => 10,
            'mes' => 1,
            'ano' => now()->subYear()->year // Ano passado com certeza está atrasado
        ]);

        $inadimplentes = Mensalidade::inadimplentes()->get();

        $this->assertCount(1, $inadimplentes);
        $this->assertEquals($dbv2->id, $inadimplentes->first()->desbravador_id);
    }
}

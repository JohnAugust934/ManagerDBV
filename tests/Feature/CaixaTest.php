<?php

namespace Tests\Feature;

use App\Models\Caixa;
use App\Models\User;
use App\Models\Club;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CaixaTest extends TestCase
{
    use RefreshDatabase;

    public function test_usuario_logado_pode_ver_o_caixa()
    {
        $clube = Club::create(['nome' => 'Clube Teste', 'cidade' => 'SP']);
        // CORREÇÃO: Define papel de tesoureiro
        $user = User::factory()->create(['club_id' => $clube->id, 'role' => 'tesoureiro']);
        Caixa::factory()->count(3)->create();

        $response = $this->actingAs($user)->get(route('caixa.index'));
        $response->assertStatus(200);
    }

    public function test_pode_criar_uma_entrada_no_caixa()
    {
        $clube = Club::create(['nome' => 'Clube Teste', 'cidade' => 'SP']);
        // CORREÇÃO: Define papel de tesoureiro
        $user = User::factory()->create(['club_id' => $clube->id, 'role' => 'tesoureiro']);

        $dados = [
            'descricao' => 'Doação da Igreja',
            'tipo' => 'entrada',
            'valor' => 150.00,
            'data_movimentacao' => now()->format('Y-m-d')
        ];

        $response = $this->actingAs($user)->post(route('caixa.store'), $dados);

        $response->assertRedirect(route('caixa.index'));
        $this->assertDatabaseHas('caixas', ['descricao' => 'Doação da Igreja', 'valor' => 150.00]);
    }
}

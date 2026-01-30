<?php

namespace Tests\Feature;

use App\Models\Club;
use App\Models\Desbravador;
use App\Models\Unidade;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FrequenciaTest extends TestCase
{
    use RefreshDatabase;

    public function test_pode_acessar_tela_de_chamada()
    {
        $clube = Club::create(['nome' => 'Clube Teste', 'cidade' => 'SP']);
        // Usamos Master para garantir que o acesso à tela funcione
        $user = User::factory()->create(['club_id' => $clube->id, 'role' => 'master']);

        $unidade = Unidade::factory()->create();
        Desbravador::factory()->create(['unidade_id' => $unidade->id]);

        $response = $this->actingAs($user)->get(route('frequencia.create'));

        $response->assertStatus(200);
    }

    public function test_pode_salvar_chamada_e_calcular_pontos()
    {
        $clube = Club::create(['nome' => 'Clube Teste', 'cidade' => 'SP']);

        // 1. Cria usuário Master (Acesso total garantido)
        $user = User::factory()->create([
            'club_id' => $clube->id,
            'role' => 'master'
        ]);

        $unidade = Unidade::factory()->create();
        $dbv = Desbravador::factory()->create(['unidade_id' => $unidade->id, 'ativo' => true]);

        // 2. Envia dados simulando o formulário HTML
        // O formato presencas[id][campo] é crucial
        $dados = [
            'data' => now()->format('Y-m-d'),
            'unidade_id' => $unidade->id,
            'presencas' => [
                $dbv->id => [
                    'presente' => 'on', // Checkbox checked envia valor
                    'pontual' => 'on',
                    'biblia' => 'on',
                    'uniforme' => 'on'
                ]
            ]
        ];

        $response = $this->actingAs($user)->post(route('frequencia.store'), $dados);

        // 3. Verifica redirecionamento
        $response->assertRedirect(route('dashboard'));

        // 4. Verifica banco de dados
        $this->assertDatabaseHas('frequencias', [
            'desbravador_id' => $dbv->id,
            'presente' => true, // Laravel casta 'on' ou 1 para true no boolean
            'pontual' => true
        ]);
    }
}

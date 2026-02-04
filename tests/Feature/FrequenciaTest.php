<?php

namespace Tests\Feature;

use App\Models\Club;
use App\Models\Desbravador;
use App\Models\Frequencia;
use App\Models\Unidade;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FrequenciaTest extends TestCase
{
    use RefreshDatabase;

    public function test_pode_acessar_historico_frequencia()
    {
        $clube = Club::create(['nome' => 'Clube Teste', 'cidade' => 'SP']);
        $user = User::factory()->create(['club_id' => $clube->id, 'role' => 'secretario']);

        $response = $this->actingAs($user)->get(route('frequencia.index'));

        $response->assertStatus(200);
        $response->assertSee('Histórico de Frequência');
    }

    public function test_historico_filtra_por_mes_e_ano()
    {
        $clube = Club::create(['nome' => 'Clube Teste', 'cidade' => 'SP']);
        $user = User::factory()->create(['club_id' => $clube->id, 'role' => 'secretario']);

        $unidade = Unidade::factory()->create();
        $dbv = Desbravador::factory()->create(['unidade_id' => $unidade->id, 'ativo' => true]);

        // Cria frequência em JANEIRO de 2026
        Frequencia::create([
            'desbravador_id' => $dbv->id,
            'data' => '2026-01-15',
            'presente' => true,
        ]);

        // Acessa o filtro de JANEIRO 2026 -> Deve encontrar
        $response = $this->actingAs($user)->get(route('frequencia.index', ['mes' => 1, 'ano' => 2026]));
        $response->assertSeeText('15');
        $response->assertSee($dbv->nome);

        // Acessa o filtro de FEVEREIRO 2026 -> Não deve encontrar a reunião de janeiro
        $response2 = $this->actingAs($user)->get(route('frequencia.index', ['mes' => 2, 'ano' => 2026]));
        $response2->assertDontSeeText('15');
    }

    public function test_pode_acessar_tela_de_nova_chamada()
    {
        $clube = Club::create(['nome' => 'Clube Teste', 'cidade' => 'SP']);
        $user = User::factory()->create(['club_id' => $clube->id, 'role' => 'secretario']);

        $response = $this->actingAs($user)->get(route('frequencia.create'));

        $response->assertStatus(200);
        $response->assertSee('Registro de Chamada');
    }

    public function test_salvar_chamada_com_falta_gera_registro_de_ausencia()
    {
        $clube = Club::create(['nome' => 'Clube Teste', 'cidade' => 'SP']);
        $user = User::factory()->create(['club_id' => $clube->id, 'role' => 'master']);

        $unidade = Unidade::factory()->create();
        $dbv = Desbravador::factory()->create(['unidade_id' => $unidade->id, 'ativo' => true]);

        $dados = [
            'data' => now()->format('Y-m-d'),
            'presencas' => [
                $dbv->id => [
                    'registrado' => '1', // Simula o hidden input
                    // 'presente' => não enviado (checkbox desmarcado)
                ],
            ],
        ];

        $this->actingAs($user)->post(route('frequencia.store'), $dados);

        // Verifica se criou o registro no banco com presente = 0
        $this->assertDatabaseHas('frequencias', [
            'desbravador_id' => $dbv->id,
            'presente' => false, // Deve ser falso, mas o registro deve EXISTIR
        ]);
    }
}

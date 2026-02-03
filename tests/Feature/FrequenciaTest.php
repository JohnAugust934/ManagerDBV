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
        // Role correto: 'secretario' (conforme seu User.php)
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

        // Cria frequência em JANEIRO de 2026 (Dia 15)
        Frequencia::create([
            'desbravador_id' => $dbv->id,
            'data' => '2026-01-15',
            'presente' => true,
        ]);

        // 1. Acessa o filtro de JANEIRO 2026 -> Deve encontrar o dia 15
        $response = $this->actingAs($user)->get(route('frequencia.index', ['mes' => 1, 'ano' => 2026]));
        $response->assertSeeText('15'); // assertSeeText ignora classes CSS como duration-150
        $response->assertSee($dbv->nome);

        // 2. Acessa o filtro de FEVEREIRO 2026 -> Não deve encontrar o dia 15
        $response2 = $this->actingAs($user)->get(route('frequencia.index', ['mes' => 2, 'ano' => 2026]));

        // Aqui usamos assertDontSeeText para garantir que não vemos o TEXTO "15",
        // mas ignoramos o "15" que existe nas classes CSS do layout (duration-150)
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
}

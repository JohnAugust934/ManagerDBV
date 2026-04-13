<?php

namespace Tests\Feature;

use App\Models\AttendanceColumn;
use App\Models\Club;
use App\Models\Desbravador;
use App\Models\Frequencia;
use App\Models\FrequenciaColumnValue;
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
        $response->assertSee('Histórico');
    }

    public function test_historico_filtra_por_mes_e_ano()
    {
        $clube = Club::create(['nome' => 'Clube Teste', 'cidade' => 'SP']);
        $user = User::factory()->create(['club_id' => $clube->id, 'role' => 'secretario']);

        // CORREÇÃO: A unidade criada no teste precisa pertencer ao mesmo clube do usuário!
        $unidade = Unidade::factory()->create(['club_id' => $clube->id]);
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

        // CORREÇÃO: A unidade criada no teste precisa pertencer ao mesmo clube
        $unidade = Unidade::factory()->create(['club_id' => $clube->id]);
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
            'presente' => false,
        ]);
    }

    public function test_apenas_diretor_secretario_e_master_acessam_gerencia_de_colunas()
    {
        $clube = Club::create(['nome' => 'Clube Teste', 'cidade' => 'SP']);

        $diretor = User::factory()->create(['club_id' => $clube->id, 'role' => 'diretor']);
        $secretario = User::factory()->create(['club_id' => $clube->id, 'role' => 'secretario']);
        $master = User::factory()->create(['club_id' => $clube->id, 'role' => 'master']);
        $conselheiro = User::factory()->create(['club_id' => $clube->id, 'role' => 'conselheiro']);

        $this->actingAs($diretor)->get(route('frequencia.columns.index'))->assertOk();
        $this->actingAs($secretario)->get(route('frequencia.columns.index'))->assertOk();
        $this->actingAs($master)->get(route('frequencia.columns.index'))->assertOk();
        $this->actingAs($conselheiro)->get(route('frequencia.columns.index'))->assertForbidden();
    }

    public function test_coluna_personalizada_aparece_em_maiusculo_na_nova_chamada()
    {
        $clube = Club::create(['nome' => 'Clube Teste', 'cidade' => 'SP']);
        $diretor = User::factory()->create(['club_id' => $clube->id, 'role' => 'diretor']);
        $unidade = Unidade::factory()->create(['club_id' => $clube->id]);
        Desbravador::factory()->create([
            'unidade_id' => $unidade->id,
            'ativo' => true,
        ]);

        $this->actingAs($diretor)->put(route('frequencia.columns.update'), [
            'columns' => [],
            'new_columns' => [
                ['name' => 'caderno de campo', 'points' => 7],
            ],
        ])->assertRedirect(route('frequencia.columns.index'));

        $column = AttendanceColumn::where('club_id', $clube->id)
            ->where('is_fixed', false)
            ->firstOrFail();

        $response = $this->actingAs($diretor)->get(route('frequencia.create'));

        $response->assertOk();
        $response->assertSee(mb_strtoupper($column->name, 'UTF-8'));
        $response->assertSee('(7)');
    }

    public function test_pode_remover_coluna_adicional_nunca_usada()
    {
        $clube = Club::create(['nome' => 'Clube Teste', 'cidade' => 'SP']);
        $diretor = User::factory()->create(['club_id' => $clube->id, 'role' => 'diretor']);

        $column = AttendanceColumn::create([
            'club_id' => $clube->id,
            'name' => 'Caderno',
            'points' => 4,
            'is_fixed' => false,
            'is_active' => true,
            'sort_order' => 120,
        ]);

        $this->actingAs($diretor)
            ->delete(route('frequencia.columns.destroy', $column->id))
            ->assertRedirect(route('frequencia.columns.index'));

        $this->assertDatabaseMissing('attendance_columns', ['id' => $column->id]);
    }

    public function test_nao_pode_remover_coluna_adicional_ja_usada()
    {
        $clube = Club::create(['nome' => 'Clube Teste', 'cidade' => 'SP']);
        $diretor = User::factory()->create(['club_id' => $clube->id, 'role' => 'diretor']);
        $unidade = Unidade::factory()->create(['club_id' => $clube->id]);
        $dbv = Desbravador::factory()->create(['unidade_id' => $unidade->id, 'ativo' => true]);

        $column = AttendanceColumn::create([
            'club_id' => $clube->id,
            'name' => 'Caderno',
            'points' => 4,
            'is_fixed' => false,
            'is_active' => true,
            'sort_order' => 120,
        ]);

        $frequencia = Frequencia::create([
            'desbravador_id' => $dbv->id,
            'data' => now()->toDateString(),
            'presente' => true,
            'pontual' => false,
            'biblia' => false,
            'uniforme' => false,
        ]);

        FrequenciaColumnValue::create([
            'frequencia_id' => $frequencia->id,
            'attendance_column_id' => $column->id,
            'checked' => true,
            'points_awarded' => 4,
        ]);

        $this->actingAs($diretor)
            ->delete(route('frequencia.columns.destroy', $column->id))
            ->assertRedirect(route('frequencia.columns.index'));

        $this->assertDatabaseHas('attendance_columns', ['id' => $column->id]);
    }
}

<?php

namespace Tests\Feature;

use App\Models\Club;
use App\Models\Desbravador;
use App\Models\Especialidade;
use App\Models\EspecialidadeRequisito;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EspecialidadeTest extends TestCase
{
    use RefreshDatabase;

    private function autenticarInstrutor(): User
    {
        $club = Club::create(['nome' => 'Clube Teste', 'cidade' => 'SP']);

        return User::factory()->create(['club_id' => $club->id, 'role' => 'instrutor']);
    }

    public function test_usuario_pode_criar_uma_especialidade_manual(): void
    {
        $user = $this->autenticarInstrutor();

        $response = $this->actingAs($user)->post(route('especialidades.store'), [
            'nome' => 'Felinos Urbanos',
            'area' => 'Estudos da Natureza',
        ]);

        $response->assertRedirect(route('especialidades.index'));

        $this->assertDatabaseHas('especialidades', [
            'nome' => 'Felinos Urbanos',
            'area' => 'Estudos da Natureza',
            'is_oficial' => false,
            'created_by' => $user->id,
        ]);
    }

    public function test_permte_nome_repetido_em_areas_diferentes(): void
    {
        $user = $this->autenticarInstrutor();

        Especialidade::create(['nome' => 'Primeiros Socorros', 'area' => 'Ciência e Saúde']);

        $response = $this->actingAs($user)->post(route('especialidades.store'), [
            'nome' => 'Primeiros Socorros',
            'area' => 'Atividades Recreativas',
        ]);

        $response->assertSessionDoesntHaveErrors();
        $this->assertDatabaseHas('especialidades', [
            'nome' => 'Primeiros Socorros',
            'area' => 'Atividades Recreativas',
        ]);
    }

    public function test_bloqueia_nome_repetido_na_mesma_area(): void
    {
        $user = $this->autenticarInstrutor();

        Especialidade::create(['nome' => 'Cães', 'area' => 'Estudos da Natureza']);

        $response = $this->actingAs($user)->post(route('especialidades.store'), [
            'nome' => 'Cães',
            'area' => 'Estudos da Natureza',
        ]);

        $response->assertSessionHasErrors(['nome']);
    }

    public function test_busca_acento_insensivel_funciona(): void
    {
        $user = $this->autenticarInstrutor();

        Especialidade::create([
            'nome' => 'Ciência Planetária',
            'area' => 'Estudos da Natureza',
            'nome_search' => 'ciencia planetaria',
            'area_search' => 'estudos da natureza',
        ]);

        Especialidade::create([
            'nome' => 'Música - avançado',
            'area' => 'Artes e Habilidades Manuais',
            'nome_search' => 'musica - avancado',
            'area_search' => 'artes e habilidades manuais',
        ]);

        $response = $this->actingAs($user)->get(route('especialidades.index', ['search' => 'ciencia']));

        $response->assertStatus(200);
        $response->assertSee('Ciência Planetária');
        $response->assertDontSee('Música - avançado');
    }

    public function test_filtros_por_categoria_avancada_e_investidos_funcionam(): void
    {
        $user = $this->autenticarInstrutor();

        $avancada = Especialidade::create([
            'nome' => 'Música - avançado',
            'area' => 'Artes e Habilidades Manuais',
            'is_avancada' => true,
            'nome_search' => 'musica - avancado',
            'area_search' => 'artes e habilidades manuais',
        ]);

        $regular = Especialidade::create([
            'nome' => 'Música - básico',
            'area' => 'Artes e Habilidades Manuais',
            'is_avancada' => false,
            'nome_search' => 'musica - basico',
            'area_search' => 'artes e habilidades manuais',
        ]);

        $desbravador = Desbravador::factory()->create();
        $desbravador->especialidades()->attach($avancada->id, ['data_conclusao' => now()->toDateString()]);

        $response = $this->actingAs($user)->get(route('especialidades.index', [
            'area' => 'Artes e Habilidades Manuais',
            'avancadas' => '1',
            'investidos' => 'com',
        ]));

        $response->assertStatus(200);
        $response->assertSee('Música - avançado');
        $response->assertDontSee('Música - básico');

        $response2 = $this->actingAs($user)->get(route('especialidades.index', [
            'area' => 'Artes e Habilidades Manuais',
            'investidos' => 'sem',
        ]));

        $response2->assertStatus(200);
        $response2->assertSee('Música - básico');
        $response2->assertDontSee('Música - avançado');

        $this->assertNotNull($regular->id);
    }

    public function test_tela_de_detalhes_exibe_requisitos_oficiais(): void
    {
        $user = $this->autenticarInstrutor();

        $especialidade = Especialidade::create([
            'nome' => 'Arte de Acampar',
            'area' => 'Atividades Recreativas',
            'codigo' => 'AR-001',
            'is_oficial' => true,
            'nome_search' => 'arte de acampar',
            'area_search' => 'atividades recreativas',
        ]);

        EspecialidadeRequisito::create([
            'especialidade_id' => $especialidade->id,
            'ordem' => 1,
            'descricao' => 'Explicar como escolher um local de acampamento.',
        ]);

        $response = $this->actingAs($user)->get(route('especialidades.show', $especialidade));

        $response->assertStatus(200);
        $response->assertSee('AR-001');
        $response->assertSee('Requisitos Oficiais');
        $response->assertSee('Explicar como escolher um local de acampamento.');
    }
}

<?php

namespace Tests\Feature;

use App\Models\Club;
use App\Models\Especialidade;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EspecialidadeTest extends TestCase
{
    use RefreshDatabase;

    public function test_usuario_pode_criar_uma_especialidade()
    {
        $club = Club::create(['nome' => 'Clube Teste', 'cidade' => 'SP']);
        // Instrutor ou superior pode criar
        $user = User::factory()->create(['club_id' => $club->id, 'role' => 'instrutor']);

        $dados = [
            'nome' => 'Felinos',
            'area' => 'Estudo da Natureza',
        ];

        $response = $this->actingAs($user)->post(route('especialidades.store'), $dados);

        $response->assertRedirect(route('especialidades.index'));
        $this->assertDatabaseHas('especialidades', ['nome' => 'Felinos']);
    }

    public function test_valida_duplicidade_de_nome()
    {
        $club = Club::create(['nome' => 'Clube Teste', 'cidade' => 'SP']);
        $user = User::factory()->create(['club_id' => $club->id, 'role' => 'instrutor']);

        Especialidade::create(['nome' => 'Cães', 'area' => 'Estudo da Natureza']);

        $response = $this->actingAs($user)->post(route('especialidades.store'), [
            'nome' => 'Cães',
            'area' => 'Outra',
        ]);

        $response->assertSessionHasErrors(['nome']);
    }

    public function test_busca_especialidade_filtra_corretamente()
    {
        $club = Club::create(['nome' => 'Clube Busca', 'cidade' => 'RJ']);

        // Garante permissão de visualização (role instrutor ou similar)
        $user = User::factory()->create(['club_id' => $club->id, 'role' => 'instrutor']);

        Especialidade::create(['nome' => 'Nós e Amarras', 'area' => 'Atividades Recreativas']);
        Especialidade::create(['nome' => 'Primeiros Socorros', 'area' => 'Saúde']);

        // Busca por parte do nome (case insensitive)
        $response = $this->actingAs($user)->get(route('especialidades.index', ['search' => 'amarras']));

        $response->assertStatus(200);
        $response->assertSee('Nós e Amarras');
        $response->assertDontSee('Primeiros Socorros');
    }
}

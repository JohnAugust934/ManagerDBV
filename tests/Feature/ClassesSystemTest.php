<?php

namespace Tests\Feature;

use App\Models\Classe;
use App\Models\Club;
use App\Models\Desbravador;
use App\Models\User;
use App\Services\ClubContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClassesSystemTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A edição do catálogo de requisitos é da plataforma (Fase 6): autentica um
     * platform admin em modo suporte a um clube.
     */
    private function autenticarAdminEmSuporte(): User
    {
        $club = Club::create(['nome' => 'Clube Suporte', 'cidade' => 'SP']);
        $admin = User::factory()->platformAdmin()->create(['club_id' => null]);

        $this->actingAs($admin);
        session([ClubContext::SESSION_KEY => $club->id]);

        return $admin;
    }

    public function test_desbravador_aparece_apenas_na_classe_que_esta_vinculado()
    {
        $this->seed(\Database\Seeders\ClassesSeeder::class);

        $clube = Club::create(['nome' => 'Clube Teste', 'cidade' => 'SP']);
        $user = User::factory()->create(['club_id' => $clube->id, 'role' => 'instrutor']);

        $classeAmigo = Classe::where('nome', 'Amigo')->first();
        $classeCompanheiro = Classe::where('nome', 'Companheiro')->first();

        // Desbravador deve pertencer ao clube para o GlobalScope encontrá-lo.
        $dbv = Desbravador::factory()->forClube($clube->id)->create([
            'nome' => 'Joaozinho',
            'classe_atual' => $classeAmigo->id,
            'ativo' => true,
        ]);

        $response = $this->actingAs($user)->get(route('classes.show', $classeAmigo->id));
        $response->assertStatus(200);
        $response->assertSee('Joaozinho');

        $response = $this->actingAs($user)->get(route('classes.show', $classeCompanheiro->id));
        $response->assertStatus(200);
        $response->assertDontSee('Joaozinho');

        $dbv->update(['classe_atual' => $classeCompanheiro->id]);

        $this->actingAs($user)->get(route('classes.show', $classeCompanheiro->id))
            ->assertSee('Joaozinho');
    }

    public function test_instrutor_pode_assinar_requisito()
    {
        $this->seed(\Database\Seeders\ClassesSeeder::class);
        $clube = Club::create(['nome' => 'Clube Teste', 'cidade' => 'SP']);
        $user = User::factory()->create(['club_id' => $clube->id, 'role' => 'instrutor']);

        $classe = Classe::where('nome', 'Amigo')->first();
        $req = $classe->requisitos->first();

        // Desbravador deve pertencer ao clube para o GlobalScope encontrá-lo.
        $dbv = Desbravador::factory()->forClube($clube->id)->create([
            'classe_atual' => $classe->id,
            'ativo' => true,
        ]);

        $response = $this->actingAs($user)->postJson(route('classes.toggle'), [
            'desbravador_id' => $dbv->id,
            'requisito_id' => $req->id,
            'concluido' => true,
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('desbravador_requisito', [
            'desbravador_id' => $dbv->id,
            'requisito_id' => $req->id,
        ]);
    }

    public function test_instrutor_pode_desmarcar_requisito()
    {
        $this->seed(\Database\Seeders\ClassesSeeder::class);
        $clube = Club::create(['nome' => 'Clube Teste', 'cidade' => 'SP']);
        $user = User::factory()->create(['club_id' => $clube->id, 'role' => 'instrutor']);
        $classe = Classe::where('nome', 'Amigo')->first();
        $req = $classe->requisitos->first();
        $dbv = Desbravador::factory()->forClube($clube->id)->create(['classe_atual' => $classe->id, 'ativo' => true]);
        $dbv->requisitosCumpridos()->attach($req->id, ['user_id' => $user->id, 'data_conclusao' => now()->toDateString()]);

        $response = $this->actingAs($user)->postJson(route('classes.toggle'), [
            'desbravador_id' => $dbv->id,
            'requisito_id' => $req->id,
            'concluido' => false,
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseMissing('desbravador_requisito', [
            'desbravador_id' => $dbv->id,
            'requisito_id' => $req->id,
        ]);
    }

    public function test_pode_criar_requisito_em_classe()
    {
        $this->autenticarAdminEmSuporte();
        $classe = Classe::factory()->create();

        $response = $this->post(route('classes.requisitos.store', $classe), [
            'descricao' => 'Aprender nó quadrado',
            'codigo' => 'REQ-01',
            'categoria' => 'Habilidades',
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('requisitos', [
            'classe_id' => $classe->id,
            'descricao' => 'Aprender nó quadrado',
        ]);
    }

    public function test_pode_atualizar_requisito()
    {
        $this->autenticarAdminEmSuporte();
        $classe = Classe::factory()->create();
        $requisito = $classe->requisitos()->create([
            'descricao' => 'Descrição original',
            'codigo' => 'R01',
            'categoria' => 'Geral',
        ]);

        $response = $this->put(route('classes.requisitos.update', [$classe, $requisito]), [
            'descricao' => 'Descrição atualizada',
            'codigo' => 'R01',
            'categoria' => 'Geral',
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('requisitos', [
            'id' => $requisito->id,
            'descricao' => 'Descrição atualizada',
        ]);
    }

    public function test_pode_excluir_requisito()
    {
        $this->autenticarAdminEmSuporte();
        $classe = Classe::factory()->create();
        $requisito = $classe->requisitos()->create([
            'descricao' => 'Requisito a excluir',
            'codigo' => 'R99',
            'categoria' => 'Geral',
        ]);

        $response = $this->delete(route('classes.requisitos.destroy', [$classe, $requisito]));

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseMissing('requisitos', ['id' => $requisito->id]);
    }
}

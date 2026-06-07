<?php

namespace Tests\Feature;

use App\Models\Classe;
use App\Models\Club;
use App\Models\Desbravador;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClassesSystemTest extends TestCase
{
    use RefreshDatabase;

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
}

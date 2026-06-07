<?php

namespace Tests\Feature;

use App\Models\Ato;
use App\Models\Club;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AtoTest extends TestCase
{
    use RefreshDatabase;

    public function test_usuario_pode_ver_lista_de_atos()
    {
        $clube = Club::create(['nome' => 'Clube Teste', 'cidade' => 'SP']);
        $user = User::factory()->create(['club_id' => $clube->id, 'role' => 'secretario']);

        Ato::factory()->count(2)->create(['club_id' => $clube->id]);

        $response = $this->actingAs($user)->get(route('atos.index'));
        $response->assertStatus(200);
    }

    public function test_usuario_pode_registrar_um_ato_administrativo()
    {
        $clube = Club::create(['nome' => 'Clube Teste', 'cidade' => 'SP']);
        $user = User::factory()->create(['club_id' => $clube->id, 'role' => 'secretario']);

        $dados = [
            'numero' => '001/2026',
            'tipo' => 'Nomeação',
            'data' => now()->format('Y-m-d'),
            'descricao' => 'Fica nomeado fulano de tal para o cargo de conselheiro.',
        ];

        $response = $this->actingAs($user)->post(route('atos.store'), $dados);

        $response->assertRedirect(route('atos.index'));

        $this->assertDatabaseHas('atos', [
            'numero' => '001/2026',
            'descricao' => 'Fica nomeado fulano de tal para o cargo de conselheiro.',
            'club_id' => $clube->id,
        ]);
    }

    public function test_usuario_pode_editar_um_ato_administrativo()
    {
        $clube = Club::create(['nome' => 'Clube Teste', 'cidade' => 'SP']);
        $user = User::factory()->create(['club_id' => $clube->id, 'role' => 'secretario']);
        $ato = Ato::factory()->create([
            'numero' => '001/2026',
            'tipo' => 'Nomeacao',
            'descricao' => 'Texto original',
            'club_id' => $clube->id,
        ]);

        $response = $this->actingAs($user)->put(route('atos.update', $ato), [
            'numero' => '002/2026',
            'tipo' => 'Voto',
            'data' => now()->format('Y-m-d'),
            'descricao' => 'Texto atualizado do ato.',
        ]);

        $response->assertRedirect(route('atos.index'));
        $this->assertDatabaseHas('atos', [
            'id' => $ato->id,
            'numero' => '002/2026',
            'tipo' => 'Voto',
            'descricao' => 'Texto atualizado do ato.',
        ]);
    }

    public function test_usuario_pode_excluir_um_ato_administrativo()
    {
        $clube = Club::create(['nome' => 'Clube Teste', 'cidade' => 'SP']);
        $user = User::factory()->create(['club_id' => $clube->id, 'role' => 'secretario']);
        $ato = Ato::factory()->create(['club_id' => $clube->id]);

        $response = $this->actingAs($user)->delete(route('atos.destroy', $ato));

        $response->assertRedirect(route('atos.index'));
        $this->assertDatabaseMissing('atos', ['id' => $ato->id]);
    }
}

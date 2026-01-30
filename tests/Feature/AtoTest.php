<?php

namespace Tests\Feature;

use App\Models\Ato;
use App\Models\User;
use App\Models\Club;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AtoTest extends TestCase
{
    use RefreshDatabase;

    public function test_usuario_pode_ver_lista_de_atos()
    {
        $clube = Club::create(['nome' => 'Clube Teste', 'cidade' => 'SP']);
        // CORREÇÃO: Define papel de secretaria
        $user = User::factory()->create(['club_id' => $clube->id, 'role' => 'secretario']);
        Ato::factory()->count(2)->create();

        $response = $this->actingAs($user)->get(route('atos.index'));
        $response->assertStatus(200);
    }

    public function test_usuario_pode_registrar_um_ato_administrativo()
    {
        $clube = Club::create(['nome' => 'Clube Teste', 'cidade' => 'SP']);
        // CORREÇÃO: Define papel de secretaria
        $user = User::factory()->create(['club_id' => $clube->id, 'role' => 'secretario']);

        $dados = [
            'tipo' => 'Nomeação',
            'data' => now()->format('Y-m-d'),
            'descricao_resumida' => 'Nomeação de Conselheiro',
            'texto_completo' => 'Fica nomeado fulano de tal...'
        ];

        $response = $this->actingAs($user)->post(route('atos.store'), $dados);

        $response->assertRedirect(route('atos.index'));
        $this->assertDatabaseHas('atos', ['descricao_resumida' => 'Nomeação de Conselheiro']);
    }
}

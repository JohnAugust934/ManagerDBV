<?php

namespace Tests\Feature;

use App\Models\Club;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SobreTest extends TestCase
{
    use RefreshDatabase;

    public function test_aba_sobre_pode_ser_acessada_por_usuarios_logados()
    {
        $club = Club::create(['nome' => 'Clube Teste', 'cidade' => 'SP']);
        $user = User::factory()->create(['club_id' => $club->id, 'role' => 'conselheiro']);

        $response = $this->actingAs($user)->get(route('sobre'));

        $response->assertStatus(200);
        $response->assertSee('Versão Atual');
        $response->assertSee('v1.2.0-beta');
    }

    public function test_aba_sobre_redireciona_convidados()
    {
        $response = $this->get(route('sobre'));

        $response->assertRedirect(route('login'));
    }
}

<?php

namespace Tests\Feature;

use App\Models\Invitation;
use App\Models\User;
use App\Models\Club;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InviteRegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_registration_is_disabled()
    {
        $response = $this->get('/register');

        $this->assertTrue(
            in_array($response->status(), [403, 404, 302]),
            "A rota /register deveria estar desativada."
        );
    }

    public function test_first_user_must_create_club()
    {
        // Cria convite para DIRETOR
        $invite = Invitation::create([
            'email' => 'diretor@teste.com',
            'token' => 'token-diretor',
            'role' => 'diretor'
        ]);

        $response = $this->post(route('register.store_invite'), [
            'token' => 'token-diretor',
            'name' => 'Diretor Teste',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        // CORREÇÃO: Diretor é redirecionado para editar o clube, não para o dashboard
        $response->assertRedirect(route('club.edit'));

        $user = User::where('email', 'diretor@teste.com')->first();
        $this->assertNotNull($user->club_id); // Clube criado automaticamente
        $this->assertEquals('diretor', $user->role);
    }

    public function test_second_user_joins_existing_club()
    {
        $club = Club::create(['nome' => 'Clube Teste', 'cidade' => 'SP']);

        // Convite para CONSELHEIRO (entra em clube existente)
        $invite = Invitation::create([
            'email' => 'conselheiro@teste.com',
            'token' => 'token-conselheiro',
            'role' => 'conselheiro',
            'club_id' => $club->id
        ]);

        $response = $this->post(route('register.store_invite'), [
            'token' => 'token-conselheiro',
            'name' => 'Conselheiro Teste',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        // Conselheiros continuam indo para o Dashboard
        $response->assertRedirect(route('dashboard'));

        $user = User::where('email', 'conselheiro@teste.com')->first();
        $this->assertEquals($club->id, $user->club_id);
        $this->assertEquals('conselheiro', $user->role);
    }
}

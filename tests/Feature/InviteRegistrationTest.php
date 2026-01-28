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
        $response->assertRedirect(route('login'));
    }

    public function test_first_user_must_create_club()
    {
        // Cenário: Nenhum clube existe
        $this->assertEquals(0, Club::count());

        $token = 'token-diretor';
        Invitation::create(['email' => 'diretor@teste.com', 'token' => $token]);

        // A tela deve pedir dados do clube ($needsClubSetup = true)
        $response = $this->get(route('register', ['token' => $token]));
        $response->assertSee('Dados do Clube');
        $response->assertSee('Fundar Clube');

        // Registro
        $this->post(route('register'), [
            'token' => $token,
            'name' => 'Diretor',
            'email' => 'diretor@teste.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'club_name' => 'Clube Pioneiros',
            'club_city' => 'SP'
        ]);

        $this->assertDatabaseHas('clubs', ['nome' => 'Clube Pioneiros']);
        $this->assertDatabaseHas('users', ['email' => 'diretor@teste.com', 'club_id' => Club::first()->id]);
    }

    public function test_second_user_joins_existing_club()
    {
        // 1. Preparação: Já existe um clube e um diretor
        $club = Club::create(['nome' => 'Clube Existente', 'cidade' => 'Rio']);
        $this->assertEquals(1, Club::count());

        // 2. Novo convite para um conselheiro
        $token = 'token-conselheiro';
        Invitation::create(['email' => 'conselheiro@teste.com', 'token' => $token]);

        // A tela NÃO deve pedir dados do clube
        $response = $this->get(route('register', ['token' => $token]));
        $response->assertDontSee('Dados do Clube');
        $response->assertSee('Você será adicionado ao clube');

        // 3. Registro (sem enviar dados de clube)
        $response = $this->post(route('register'), [
            'token' => $token,
            'name' => 'Conselheiro',
            'email' => 'conselheiro@teste.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            // Note que NÃO enviamos club_name nem club_city
        ]);

        $response->assertRedirect(route('dashboard'));

        // 4. Verifica se entrou no clube certo
        $user = User::where('email', 'conselheiro@teste.com')->first();
        $this->assertEquals($club->id, $user->club_id);

        // Garante que não criou outro clube duplicado
        $this->assertEquals(1, Club::count());
    }
}

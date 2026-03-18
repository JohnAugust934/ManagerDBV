<?php

namespace Tests\Feature;

use App\Models\Club;
use App\Models\Invitation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InviteRegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_registration_is_disabled()
    {
        // Garante que o acesso direto à URL de registro pública redirecione o usuário (302)
        // para fora (provavelmente para o login) ou dê erro 404.
        // Isso prova que a rota foi desativada e a tela não é renderizada (200).
        $response = $this->get('/register');
        $this->assertContains($response->status(), [302, 404]);
    }

    public function test_first_user_must_create_club()
    {
        $invite = Invitation::create([
            'email' => 'diretor@teste.com',
            'token' => 'token123',
            'role' => 'diretor',
            'expires_at' => now()->addDays(7),
        ]);

        $response = $this->post(route('register.store_invite'), [
            'token' => 'token123',
            'name' => 'Diretor Teste',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertRedirect(route('club.edit'));

        $user = User::where('email', 'diretor@teste.com')->first();
        $this->assertEquals('diretor', $user->role);
        $this->assertNull($user->club_id);
    }

    public function test_second_user_joins_existing_club()
    {
        $club = Club::create(['nome' => 'Clube Teste', 'cidade' => 'SP', 'associacao' => 'APaC']);

        $invite = Invitation::create([
            'email' => 'conselheiro@teste.com',
            'token' => 'token123',
            'role' => 'conselheiro',
            'expires_at' => now()->addDays(7),
        ]);

        $response = $this->post(route('register.store_invite'), [
            'token' => 'token123',
            'name' => 'Conselheiro Teste',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertRedirect(route('dashboard'));

        $user = User::where('email', 'conselheiro@teste.com')->first();
        $this->assertEquals($club->id, $user->club_id);
        $this->assertEquals('conselheiro', $user->role);
    }

    public function test_registration_with_invite_fails_gracefully_when_email_already_exists()
    {
        $club = Club::create(['nome' => 'Clube Teste', 'cidade' => 'SP', 'associacao' => 'APaC']);

        User::factory()->create([
            'email' => 'duplicado@teste.com',
            'club_id' => $club->id,
            'role' => 'conselheiro',
        ]);

        $invite = Invitation::create([
            'email' => 'duplicado@teste.com',
            'token' => 'token-duplicado',
            'role' => 'conselheiro',
            'expires_at' => now()->addDays(7),
        ]);

        $response = $this->from(route('register.invite', ['token' => 'token-duplicado']))
            ->post(route('register.store_invite'), [
                'token' => 'token-duplicado',
                'name' => 'Outro Nome',
                'password' => 'password',
                'password_confirmation' => 'password',
            ]);

        $response->assertRedirect(route('register.invite', ['token' => 'token-duplicado']));
        $response->assertSessionHasErrors([
            'email' => 'Já existe um usuário cadastrado com este e-mail.',
        ]);

        $this->assertNull($invite->fresh()->registered_at);
        $this->assertEquals(1, User::where('email', 'duplicado@teste.com')->count());
    }
}

<?php

namespace Tests\Feature;

use App\Mail\ClubInvitation;
use App\Models\Club;
use App\Models\Invitation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class InviteSystemTest extends TestCase
{
    use RefreshDatabase;

    public function test_master_pode_criar_convite_e_envia_email()
    {
        Mail::fake();

        $club = Club::create(['nome' => 'Clube Orion', 'cidade' => 'São Paulo', 'associacao' => 'APL']);
        $master = User::factory()->create(['role' => 'master', 'club_id' => $club->id]);

        $response = $this->actingAs($master)->post(route('invites.store'), [
            'email' => 'novo@clube.com',
            'role' => 'conselheiro',
        ]);

        $response->assertRedirect(route('invites.index'));
        $this->assertDatabaseHas('invitations', [
            'email' => 'novo@clube.com',
            'role' => 'conselheiro',
            'club_id' => $club->id,
        ]);

        Mail::assertSent(ClubInvitation::class, function ($mail) {
            return $mail->hasTo('novo@clube.com');
        });
    }

    public function test_usuario_pode_se_registrar_com_convite()
    {
        $club = Club::create(['nome' => 'Clube Orion', 'cidade' => 'São Paulo', 'associacao' => 'APL']);

        $invite = Invitation::create([
            'email' => 'convidado@clube.com',
            'token' => 'token-falso-123',
            'role' => 'conselheiro',
            'club_id' => $club->id,
            'expires_at' => now()->addDays(7),
        ]);

        $response = $this->post(route('register.store_invite'), [
            'token' => 'token-falso-123',
            'name' => 'Usuário Convidado',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        // Removido o 'absolute: false' para evitar o erro 500 do Laravel Router
        $response->assertRedirect(route('dashboard'));

        $this->assertDatabaseHas('users', [
            'email' => 'convidado@clube.com',
            'role' => 'conselheiro',
            'club_id' => $club->id,
        ]);

        $inviteUsado = Invitation::where('email', 'convidado@clube.com')->first();
        $this->assertNotNull($inviteUsado->registered_at);
    }
}

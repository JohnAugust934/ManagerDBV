<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Invitation;
use App\Models\Club;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FluxoCadastroCompletoTest extends TestCase
{
    use RefreshDatabase;

    public function test_fluxo_completo_criacao_clube_e_equipe()
    {
        // 1. CENÁRIO INICIAL: SÓ EXISTE MASTER, SEM CLUBE
        $master = User::factory()->create(['role' => 'master', 'club_id' => null]);

        // 2. MASTER CONVIDA DIRETOR
        // Não enviamos club_id, o controller entende que não existe clube ainda
        $this->actingAs($master)->post(route('invites.store'), [
            'email' => 'diretor@novo.com',
            'role' => 'diretor',
        ]);

        $inviteDiretor = Invitation::where('email', 'diretor@novo.com')->first();
        $this->assertNull($inviteDiretor->club_id); // Confirma que não tem clube

        // 3. DIRETOR SE REGISTRA
        $response = $this->post(route('register.store_invite'), [
            'token' => $inviteDiretor->token,
            'name' => 'Sr. Diretor',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        // Verifica redirecionamento para CRIAÇÃO DO CLUBE
        $response->assertRedirect(route('club.edit'));

        // Verifica que o clube foi criado no banco
        $diretor = User::where('email', 'diretor@novo.com')->first();
        $this->assertNotNull($diretor->club_id);
        $clube = Club::find($diretor->club_id);
        $this->assertNotNull($clube);

        // 4. MASTER CONVIDA CONSELHEIRO
        // Agora o clube JÁ EXISTE. O controller deve pegar o ID automaticamente.
        $this->actingAs($master)->post(route('invites.store'), [
            'email' => 'conselheiro@novo.com',
            'role' => 'conselheiro',
            // Não enviamos club_id, a lógica automática deve pegar o $clube->id
        ]);

        $inviteConselheiro = Invitation::where('email', 'conselheiro@novo.com')->first();

        // AQUI ESTÁ A MÁGICA: O convite deve ter pego o ID do clube automaticamente
        $this->assertEquals($clube->id, $inviteConselheiro->club_id);

        // 5. CONSELHEIRO SE REGISTRA
        $this->post(route('register.store_invite'), [
            'token' => $inviteConselheiro->token,
            'name' => 'Conselheiro Fiel',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        // 6. VERIFICAÇÃO FINAL
        $conselheiro = User::where('email', 'conselheiro@novo.com')->first();

        $this->assertEquals($clube->id, $conselheiro->club_id);
        $this->assertEquals('conselheiro', $conselheiro->role);
    }
}

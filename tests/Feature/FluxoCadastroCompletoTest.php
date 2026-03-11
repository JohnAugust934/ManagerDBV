<?php

namespace Tests\Feature;

use App\Models\Club;
use App\Models\Invitation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class FluxoCadastroCompletoTest extends TestCase
{
    use RefreshDatabase;

    public function test_fluxo_completo_single_tenant_com_diretor()
    {
        // 1. MASTER NASCE NO BANCO SEM CLUBE (Simulando a primeira instalação)
        $master = User::factory()->create([
            'name' => 'Master User',
            'email' => 'master@teste.com',
            'role' => 'master',
            'is_master' => true,
            'club_id' => null,
        ]);

        $this->actingAs($master);

        // 2. MASTER TENTA CONVIDAR CONSELHEIRO ANTES DO CLUBE EXISTIR (DEVE FALHAR E MOSTRAR ERRO)
        $this->post(route('invites.store'), [
            'email' => 'conselheiro@teste.com',
            'role' => 'conselheiro',
        ])->assertSessionHas('error');

        // 3. MASTER CONVIDA O DIRETOR (DEVE FUNCIONAR)
        $this->post(route('invites.store'), [
            'email' => 'diretor@teste.com',
            'role' => 'diretor',
        ])->assertSessionHas('success');

        Auth::logout();

        // 4. DIRETOR CLICA NO EMAIL E SE REGISTRA
        $inviteDiretor = Invitation::where('email', 'diretor@teste.com')->first();

        $response = $this->post(route('register.store_invite'), [
            'token' => $inviteDiretor->token,
            'name' => 'João Diretor',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        // 5. ONBOARDING: DIRETOR É "SEQUESTRADO" PARA A TELA DE CRIAR CLUBE
        $response->assertRedirect(route('club.edit'));
        $this->assertAuthenticated();

        $diretorLogado = auth()->user();
        $this->assertEquals('diretor', $diretorLogado->role);
        $this->assertNull($diretorLogado->club_id);

        // 6. DIRETOR PREENCHE O FORMULÁRIO E SALVA O CLUBE (Mudança de POST para PATCH aqui!)
        $this->patch(route('club.update'), [
            'nome' => 'Clube Orion',
            'cidade' => 'Araraquara - SP',
            'associacao' => 'APaC',
        ]);

        $club = Club::first();
        $this->assertNotNull($club);

        // 7. VERIFICA A MÁGICA: Master e Diretor foram vinculados ao clube
        $this->assertEquals($club->id, $diretorLogado->fresh()->club_id);
        $this->assertEquals($club->id, $master->fresh()->club_id);

        Auth::logout();

        // 8. MASTER LOGA NOVAMENTE E AGORA CONSEGUE CONVIDAR CONSELHEIROS
        $this->actingAs($master);
        $this->post(route('invites.store'), [
            'email' => 'conselheiro@teste.com',
            'role' => 'conselheiro',
        ])->assertSessionHas('success');

        Auth::logout();

        // 9. CONSELHEIRO SE REGISTRA E É VINCULADO AO CLUBE AUTOMATICAMENTE
        $inviteConselheiro = Invitation::where('email', 'conselheiro@teste.com')->first();
        $this->post(route('register.store_invite'), [
            'token' => $inviteConselheiro->token,
            'name' => 'Pedro Conselheiro',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertRedirect(route('dashboard'));

        $conselheiro = User::where('email', 'conselheiro@teste.com')->first();
        $this->assertEquals($club->id, $conselheiro->club_id);
    }
}

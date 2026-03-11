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

    public function test_fluxo_completo_criacao_clube_e_equipe()
    {
        // 1. CLUBE E MASTER SÃO CRIADOS (Simulando o setup inicial do banco de dados)
        $clube = Club::create([
            'nome' => 'Clube Orion',
            'cidade' => 'Araraquara - SP',
            'associacao' => 'APaC',
        ]);

        $master = User::factory()->create([
            'name' => 'Master User',
            'email' => 'master@teste.com',
            'role' => 'master',
            'is_master' => true,
            'club_id' => $clube->id,
        ]);

        // Garante que o usuário nasceu como master e vinculado ao clube
        $this->assertEquals('master', $master->role);
        $this->assertTrue($master->is_master);
        $this->assertEquals($clube->id, $master->club_id);

        // Faz login com o master
        $this->actingAs($master);

        // 2. MASTER CONVIDA UM DIRETOR E UM CONSELHEIRO
        $this->post(route('invites.store'), [
            'email' => 'diretor@novo.com',
            'role' => 'diretor',
        ]);

        $this->post(route('invites.store'), [
            'email' => 'conselheiro@novo.com',
            'role' => 'conselheiro',
        ]);

        // 3. VERIFICA SE OS CONVITES EXISTEM E PERTENCEM AO CLUBE
        $this->assertDatabaseCount('invitations', 2);
        $inviteConselheiro = Invitation::where('email', 'conselheiro@novo.com')->first();

        // O convite deve ter pego o ID do clube automaticamente
        $this->assertEquals($clube->id, $inviteConselheiro->club_id);

        // Faz logout do Master para o conselheiro se registrar como visitante limpo
        Auth::logout();

        // 4. CONSELHEIRO SE REGISTRA USANDO O TOKEN DO CONVITE
        $response = $this->post(route('register.store_invite'), [
            'token' => $inviteConselheiro->token,
            'name' => 'Novo Conselheiro',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect(route('dashboard', absolute: false));

        // Verifica se ele foi logado com sucesso
        $this->assertAuthenticated();

        // 5. VERIFICA SE O CONSELHEIRO ENTROU NO SISTEMA COM OS DADOS CERTOS
        $novoUsuario = User::where('email', 'conselheiro@novo.com')->first();

        $this->assertEquals('conselheiro', $novoUsuario->role);
        $this->assertEquals($clube->id, $novoUsuario->club_id);
        $this->assertFalse($novoUsuario->is_master);

        // Verifica se o convite foi marcado como usado (data de registro preenchida)
        $inviteAtualizado = Invitation::where('email', 'conselheiro@novo.com')->first();
        $this->assertNotNull($inviteAtualizado->registered_at);
    }
}

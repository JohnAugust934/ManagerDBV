<?php

namespace Tests\Feature;

use App\Models\Club;
use App\Models\Desbravador;
use App\Models\Unidade;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LgpdTest extends TestCase
{
    use RefreshDatabase;

    protected Club $clube;

    protected User $secretaria;

    protected Unidade $unidade;

    protected function setUp(): void
    {
        parent::setUp();

        $this->clube = Club::create(['nome' => 'Clube Orion', 'cidade' => 'SP']);
        $this->unidade = Unidade::factory()->create(['club_id' => $this->clube->id]);
        $this->secretaria = User::factory()->create([
            'role' => 'secretario',
            'club_id' => $this->clube->id,
        ]);
    }

    // -------------------------------------------------------------------------
    // 2.1 — Páginas legais são públicas
    // -------------------------------------------------------------------------

    public function test_politica_privacidade_e_acessivel_sem_autenticacao(): void
    {
        $response = $this->get(route('legal.privacidade'));
        $response->assertOk();
        $response->assertSee('Política de Privacidade');
        $response->assertSee('LGPD');
    }

    public function test_termos_de_uso_e_acessivel_sem_autenticacao(): void
    {
        $response = $this->get(route('legal.termos'));
        $response->assertOk();
        $response->assertSee('Termos de Uso');
    }

    // -------------------------------------------------------------------------
    // 2.2 — Consentimento obrigatório no cadastro de desbravador
    // -------------------------------------------------------------------------

    public function test_cadastro_desbravador_sem_consentimento_e_rejeitado(): void
    {
        $response = $this->actingAs($this->secretaria)->post(route('desbravadores.store'), [
            'nome' => 'Ana Souza',
            'data_nascimento' => '2012-05-10',
            'sexo' => 'F',
            'cpf' => '123.456.789-00',
            'unidade_id' => $this->unidade->id,
            'email' => 'ana@teste.com',
            'endereco' => 'Rua A, 1',
            'nome_responsavel' => 'Maria Souza',
            'telefone_responsavel' => '11999999999',
            'numero_sus' => '123456789',
            // sem consentimento_lgpd nem consentimento_lgpd_responsavel
        ]);

        $response->assertSessionHasErrors(['consentimento_lgpd', 'consentimento_lgpd_responsavel']);
        $this->assertDatabaseMissing('desbravadores', ['nome' => 'Ana Souza']);
    }

    public function test_cadastro_desbravador_com_consentimento_registra_timestamp_e_ropa(): void
    {
        $response = $this->actingAs($this->secretaria)->post(route('desbravadores.store'), [
            'nome' => 'João Silva',
            'data_nascimento' => '2013-03-15',
            'sexo' => 'M',
            'cpf' => '000.000.000-01',
            'unidade_id' => $this->unidade->id,
            'email' => 'joao@teste.com',
            'endereco' => 'Rua B, 2',
            'nome_responsavel' => 'Carlos Silva',
            'telefone_responsavel' => '11888888888',
            'numero_sus' => '987654321',
            'consentimento_lgpd' => '1',
            'consentimento_lgpd_responsavel' => 'Carlos Silva',
        ]);

        $response->assertSessionHasNoErrors();

        $desbravador = Desbravador::where('nome', 'João Silva')->first();
        $this->assertNotNull($desbravador);
        $this->assertTrue($desbravador->consentimento_lgpd);
        $this->assertNotNull($desbravador->consentimento_lgpd_em);
        $this->assertEquals('Carlos Silva', $desbravador->consentimento_lgpd_responsavel);

        // ROPA deve ter sido registrado
        $this->assertDatabaseHas('lgpd_registros', [
            'acao' => 'consentimento',
            'entidade' => 'desbravador',
            'entidade_id' => $desbravador->id,
        ]);
    }

    // -------------------------------------------------------------------------
    // 2.4 — ROPA registrado na exclusão
    // -------------------------------------------------------------------------

    public function test_exclusao_de_desbravador_registra_ropa(): void
    {
        $desbravador = Desbravador::factory()->create([
            'unidade_id' => $this->unidade->id,
            'consentimento_lgpd' => true,
            'consentimento_lgpd_responsavel' => 'Resp.',
            'consentimento_lgpd_em' => now(),
        ]);

        $id = $desbravador->id;

        $this->actingAs($this->secretaria)
            ->delete(route('desbravadores.destroy', $desbravador));

        $this->assertDatabaseMissing('desbravadores', ['id' => $id]);

        $this->assertDatabaseHas('lgpd_registros', [
            'acao' => 'exclusao',
            'entidade' => 'desbravador',
            'entidade_id' => $id,
        ]);
    }

    // -------------------------------------------------------------------------
    // 2.3 — Exportação de dados (portal do titular)
    // -------------------------------------------------------------------------

    public function test_exportacao_de_dados_retorna_json_e_registra_ropa(): void
    {
        $desbravador = Desbravador::factory()->create([
            'unidade_id' => $this->unidade->id,
            'consentimento_lgpd' => true,
            'consentimento_lgpd_responsavel' => 'Resp.',
            'consentimento_lgpd_em' => now(),
        ]);

        $response = $this->actingAs($this->secretaria)
            ->get(route('desbravadores.exportar-dados', $desbravador));

        $response->assertOk();
        $response->assertHeader('content-type', 'application/json');

        $json = $response->json();
        $this->assertArrayHasKey('titular', $json);
        $this->assertArrayHasKey('saude', $json);
        $this->assertArrayHasKey('base_legal', $json);

        $this->assertDatabaseHas('lgpd_registros', [
            'acao' => 'exportacao',
            'entidade' => 'desbravador',
            'entidade_id' => $desbravador->id,
        ]);
    }

    public function test_exportacao_de_dados_de_outro_clube_e_negada(): void
    {
        $outroClube = Club::create(['nome' => 'Clube B', 'cidade' => 'RJ']);
        $outraUnidade = Unidade::factory()->create(['club_id' => $outroClube->id]);
        $desbravadorExterno = Desbravador::factory()->create([
            'unidade_id' => $outraUnidade->id,
        ]);

        // GlobalScope deve negar acesso ao desbravador de outro clube
        $response = $this->actingAs($this->secretaria)
            ->get(route('desbravadores.exportar-dados', $desbravadorExterno));

        $response->assertStatus(404);
    }

    // -------------------------------------------------------------------------
    // 2.7 — Aceite de termos no registro por convite
    // -------------------------------------------------------------------------

    public function test_registro_por_convite_exige_aceite_dos_termos(): void
    {
        $convite = \App\Models\Invitation::create([
            'email' => 'novo@clube.com',
            'token' => 'token-test-lgpd',
            'role' => 'conselheiro',
            'club_id' => $this->clube->id,
            'expires_at' => now()->addDays(7),
        ]);

        $response = $this->post(route('register.store_invite'), [
            'token' => 'token-test-lgpd',
            'name' => 'Novo Usuário',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            // sem aceite_termos
        ]);

        $response->assertSessionHasErrors('aceite_termos');
        $this->assertDatabaseMissing('users', ['email' => 'novo@clube.com']);
    }

    public function test_registro_por_convite_com_aceite_registra_timestamp(): void
    {
        $convite = \App\Models\Invitation::create([
            'email' => 'aceite@clube.com',
            'token' => 'token-aceite-ok',
            'role' => 'conselheiro',
            'club_id' => $this->clube->id,
            'expires_at' => now()->addDays(7),
        ]);

        $response = $this->post(route('register.store_invite'), [
            'token' => 'token-aceite-ok',
            'name' => 'Conselheiro Aceite',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'aceite_termos' => '1',
        ]);

        $response->assertSessionHasNoErrors();

        $user = User::where('email', 'aceite@clube.com')->first();
        $this->assertNotNull($user);
        $this->assertNotNull($user->termos_aceitos_em);
    }

    // -------------------------------------------------------------------------
    // 2.8 — Aceite retroativo de termos para usuários existentes
    // -------------------------------------------------------------------------

    public function test_usuario_sem_aceite_e_redirecionado_para_aceitar_termos(): void
    {
        $usuario = User::factory()->semTermosAceitos()->create([
            'role' => 'secretario',
            'club_id' => $this->clube->id,
        ]);

        $response = $this->actingAs($usuario)->get(route('dashboard'));

        $response->assertRedirect(route('termos.aceitar'));
    }

    public function test_usuario_que_ja_aceitou_nao_e_redirecionado(): void
    {
        // $this->secretaria já vem com termos_aceitos_em (default do factory).
        $response = $this->actingAs($this->secretaria)->get(route('dashboard'));

        $response->assertOk();
    }

    public function test_tela_de_aceite_redireciona_quem_ja_aceitou(): void
    {
        $response = $this->actingAs($this->secretaria)->get(route('termos.aceitar'));

        $response->assertRedirect(route('dashboard'));
    }

    public function test_aceite_sem_marcar_checkbox_e_rejeitado(): void
    {
        $usuario = User::factory()->semTermosAceitos()->create([
            'role' => 'secretario',
            'club_id' => $this->clube->id,
        ]);

        $response = $this->actingAs($usuario)->post(route('termos.aceitar.store'), []);

        $response->assertSessionHasErrors('aceite_termos');
        $this->assertNull($usuario->fresh()->termos_aceitos_em);
    }

    public function test_aceite_marcado_grava_timestamp_e_registra_ropa(): void
    {
        $usuario = User::factory()->semTermosAceitos()->create([
            'role' => 'secretario',
            'club_id' => $this->clube->id,
        ]);

        $response = $this->actingAs($usuario)->post(route('termos.aceitar.store'), [
            'aceite_termos' => '1',
        ]);

        $response->assertRedirect(route('dashboard'));

        $this->assertNotNull($usuario->fresh()->termos_aceitos_em);

        $this->assertDatabaseHas('lgpd_registros', [
            'acao' => 'consentimento',
            'entidade' => 'usuario',
            'entidade_id' => $usuario->id,
            'user_id' => $usuario->id,
        ]);
    }

    // -------------------------------------------------------------------------
    // 2.6 — Comando de anonimização
    // -------------------------------------------------------------------------

    public function test_comando_anonimizar_desligados_dry_run_nao_altera_dados(): void
    {
        $desbravador = Desbravador::factory()->create([
            'unidade_id' => $this->unidade->id,
            'nome' => 'Inativo Antigo',
            'ativo' => false,
            'consentimento_lgpd_responsavel' => null, // ainda não anonimizado
        ]);

        // Simula 6 anos atrás
        $desbravador->update(['updated_at' => now()->subYears(6)]);

        $this->artisan('lgpd:anonimizar-desligados', ['--anos' => 5, '--dry-run' => true])
            ->assertExitCode(0);

        // Dados não devem ter mudado
        $this->assertDatabaseHas('desbravadores', ['id' => $desbravador->id, 'nome' => 'Inativo Antigo']);
    }
}

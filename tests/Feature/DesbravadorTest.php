<?php

namespace Tests\Feature;

use App\Models\Classe;
use App\Models\Club;
use App\Models\Desbravador;
use App\Models\Especialidade;
use App\Models\Evento;
use App\Models\Frequencia;
use App\Models\Unidade;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DesbravadorTest extends TestCase
{
    use RefreshDatabase;

    public function test_pode_criar_um_desbravador_com_campos_obrigatorios()
    {
        $clube = Club::create(['nome' => 'Clube Teste', 'cidade' => 'SP']);
        $user = User::factory()->create(['club_id' => $clube->id, 'role' => 'secretario']);

        // Unidade deve pertencer ao clube do usuário para passar na validação.
        $unidade = Unidade::factory()->create(['club_id' => $clube->id]);
        $classe = Classe::factory()->create();

        $response = $this->actingAs($user)->post(route('desbravadores.store'), [
            'nome' => 'João Desbravador',
            'data_nascimento' => '2010-01-01',
            'sexo' => 'M',
            'cpf' => '123.456.789-00',
            'rg' => '12.345.678-9',
            'unidade_id' => $unidade->id,
            'classe_atual' => $classe->id,
            'email' => 'joao@teste.com',
            'nome_responsavel' => 'Mãe do João',
            'telefone_responsavel' => '11999999999',
            'numero_sus' => '12345678900',
            'endereco' => 'Rua Teste, 123',
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect(route('desbravadores.index'));

        $this->assertDatabaseHas('desbravadores', [
            'nome' => 'João Desbravador',
            'classe_atual' => $classe->id,
            'cpf' => '123.456.789-00',
        ]);
    }

    public function test_nao_pode_cadastrar_com_unidade_de_outro_clube()
    {
        $clube = Club::create(['nome' => 'Clube A', 'cidade' => 'SP']);
        $outroClube = Club::create(['nome' => 'Clube B', 'cidade' => 'RJ']);
        $user = User::factory()->create(['club_id' => $clube->id, 'role' => 'secretario']);

        // Unidade pertence a OUTRO clube — o Rule UnidadePertenceAoClube deve rejeitar.
        $unidadeOutroClube = Unidade::factory()->create(['club_id' => $outroClube->id]);
        $classe = Classe::factory()->create();

        $response = $this->actingAs($user)->post(route('desbravadores.store'), [
            'nome' => 'João Intruso',
            'data_nascimento' => '2010-01-01',
            'sexo' => 'M',
            'cpf' => '111.222.333-44',
            'unidade_id' => $unidadeOutroClube->id,
            'classe_atual' => $classe->id,
            'email' => 'joao@teste.com',
            'nome_responsavel' => 'Mãe do João',
            'telefone_responsavel' => '11999999999',
            'numero_sus' => '12345678900',
            'endereco' => 'Rua Teste, 123',
        ]);

        $response->assertSessionHasErrors('unidade_id');
        $this->assertDatabaseMissing('desbravadores', ['nome' => 'João Intruso']);
    }

    public function test_nao_pode_criar_sem_sus_ou_responsavel_ou_cpf()
    {
        $clube = Club::create(['nome' => 'Clube Teste', 'cidade' => 'SP']);
        $user = User::factory()->create(['club_id' => $clube->id, 'role' => 'secretario']);
        $unidade = Unidade::factory()->create(['club_id' => $clube->id]);
        $classe = Classe::factory()->create();

        $response = $this->actingAs($user)->post(route('desbravadores.store'), [
            'nome' => 'João Sem Dados',
            'data_nascimento' => '2010-01-01',
            'sexo' => 'M',
            'unidade_id' => $unidade->id,
            'classe_atual' => $classe->id,
            // Faltando SUS, Responsável e CPF propositalmente
        ]);

        $response->assertSessionHasErrors(['numero_sus', 'nome_responsavel', 'cpf']);
    }

    public function test_pode_editar_desbravador()
    {
        $clube = Club::create(['nome' => 'Clube Teste', 'cidade' => 'SP']);
        $user = User::factory()->create(['club_id' => $clube->id, 'role' => 'secretario']);

        // Desbravador deve pertencer ao clube para o GlobalScope não filtrá-lo.
        $desbravador = Desbravador::factory()->forClube($clube->id)->create();
        $novaClasse = Classe::factory()->create();

        $response = $this->actingAs($user)->put(route('desbravadores.update', $desbravador), [
            'nome' => 'João Editado',
            'data_nascimento' => $desbravador->data_nascimento->format('Y-m-d'),
            'sexo' => 'M',
            'cpf' => $desbravador->cpf,
            'rg' => '99.999.999-X',
            'unidade_id' => $desbravador->unidade_id,
            'classe_atual' => $novaClasse->id,
            'ativo' => true,
            'email' => $desbravador->email,
            'nome_responsavel' => $desbravador->nome_responsavel,
            'telefone_responsavel' => $desbravador->telefone_responsavel,
            'numero_sus' => $desbravador->numero_sus,
            'endereco' => $desbravador->endereco,
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect(route('desbravadores.show', $desbravador));

        $this->assertDatabaseHas('desbravadores', [
            'id' => $desbravador->id,
            'nome' => 'João Editado',
            'classe_atual' => $novaClasse->id,
            'rg' => '99.999.999-X',
        ]);
    }

    public function test_pode_filtrar_desbravadores_por_status_ativo_inativo()
    {
        $clube = Club::create(['nome' => 'Clube Orion', 'cidade' => 'SP']);
        $user = User::factory()->create(['club_id' => $clube->id, 'role' => 'secretario']);

        // Desbravadores devem pertencer ao clube para o GlobalScope não filtrá-los.
        $ativo = Desbravador::factory()->forClube($clube->id)->create(['nome' => 'João Ativo', 'ativo' => true]);
        $inativo = Desbravador::factory()->forClube($clube->id)->create(['nome' => 'Maria Inativa', 'ativo' => false]);

        // Testa a aba "Ativos" (padrão)
        $response = $this->actingAs($user)->get(route('desbravadores.index'));
        $response->assertStatus(200);
        $response->assertSee('João Ativo');
        $response->assertDontSee('Maria Inativa');

        // Testa a aba "Inativos"
        $response = $this->actingAs($user)->get(route('desbravadores.index', ['status' => 'inativos']));
        $response->assertStatus(200);
        $response->assertSee('Maria Inativa');
        $response->assertDontSee('João Ativo');

        // Testa a aba "Todos"
        $response = $this->actingAs($user)->get(route('desbravadores.index', ['status' => 'todos']));
        $response->assertStatus(200);
        $response->assertSee('João Ativo');
        $response->assertSee('Maria Inativa');
    }

    public function test_pode_excluir_desbravador_sem_erro_500_e_removendo_vinculos()
    {
        $clube = Club::create(['nome' => 'Clube Teste', 'cidade' => 'SP']);
        $user = User::factory()->create(['club_id' => $clube->id, 'role' => 'secretario']);

        $desbravador = Desbravador::factory()->forClube($clube->id)->create();
        $especialidade = Especialidade::factory()->create();
        $evento = Evento::factory()->create(['club_id' => $clube->id]);

        $desbravador->especialidades()->attach($especialidade->id, ['data_conclusao' => now()->toDateString()]);
        $desbravador->eventos()->attach($evento->id, ['pago' => true, 'autorizacao_entregue' => true]);
        Frequencia::create([
            'desbravador_id' => $desbravador->id,
            'data' => now()->toDateString(),
            'presente' => true,
            'pontual' => true,
            'biblia' => true,
            'uniforme' => true,
        ]);

        $response = $this->actingAs($user)->delete(route('desbravadores.destroy', $desbravador));

        $response->assertRedirect(route('desbravadores.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('desbravadores', ['id' => $desbravador->id]);
        $this->assertDatabaseMissing('desbravador_especialidade', ['desbravador_id' => $desbravador->id]);
        $this->assertDatabaseMissing('desbravador_evento', ['desbravador_id' => $desbravador->id]);
        $this->assertDatabaseMissing('frequencias', ['desbravador_id' => $desbravador->id]);
    }

    public function test_tela_de_edicao_alerta_que_excluir_apaga_dados_e_recomenda_inativar()
    {
        $clube = Club::create(['nome' => 'Clube Teste', 'cidade' => 'SP']);
        $user = User::factory()->create(['club_id' => $clube->id, 'role' => 'secretario']);
        $desbravador = Desbravador::factory()->forClube($clube->id)->create();

        $response = $this->actingAs($user)->get(route('desbravadores.edit', $desbravador));

        $response->assertOk();
        $response->assertSeeText('Excluir remove tudo em definitivo. O mais seguro para o dia a dia é inativar o cadastro.');
        $response->assertSee('O recomendado é apenas inativar o cadastro. Deseja excluir mesmo assim?', false);
    }

    public function test_lista_de_desbravadores_nao_repete_alerta_de_sucesso()
    {
        $clube = Club::create(['nome' => 'Clube Teste', 'cidade' => 'SP']);
        $user = User::factory()->create(['club_id' => $clube->id, 'role' => 'secretario']);

        $response = $this->actingAs($user)
            ->withSession(['success' => 'Cadastro salvo com sucesso!'])
            ->get(route('desbravadores.index'));

        $response->assertOk();
        $response->assertDontSee('bg-green-50 dark:bg-green-900/30 border-l-4 border-green-500', false);
    }

    public function test_busca_localiza_nome_independente_de_maiusculas_e_minusculas()
    {
        $clube = Club::create(['nome' => 'Clube Teste', 'cidade' => 'SP']);
        $user = User::factory()->create(['club_id' => $clube->id, 'role' => 'secretario']);

        Desbravador::factory()->forClube($clube->id)->create(['nome' => 'Joao Alves', 'ativo' => true]);
        Desbravador::factory()->forClube($clube->id)->create(['nome' => 'Maria Santos', 'ativo' => true]);

        $response = $this->actingAs($user)->get(route('desbravadores.index', [
            'search' => 'joAO',
            'status' => 'todos',
        ]));

        $response->assertOk();
        $response->assertSee('Joao Alves');
        $response->assertDontSee('Maria Santos');
    }

    public function test_pode_remover_foto_do_desbravador()
    {
        Storage::fake('public');

        $clube = Club::create(['nome' => 'Clube Teste', 'cidade' => 'SP']);
        $user = User::factory()->create(['club_id' => $clube->id, 'role' => 'secretario']);

        Storage::disk('public')->put('fotos/foto-teste.jpg', 'conteudo-fake');
        $desbravador = Desbravador::factory()->forClube($clube->id)->create(['foto' => 'fotos/foto-teste.jpg']);

        $response = $this->actingAs($user)->delete(route('desbravadores.remover-foto', $desbravador));

        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertNull($desbravador->fresh()->foto);
        Storage::disk('public')->assertMissing('fotos/foto-teste.jpg');
    }

    public function test_pode_acessar_pagina_de_gerenciar_especialidades()
    {
        $clube = Club::create(['nome' => 'Clube Teste', 'cidade' => 'SP']);
        $user = User::factory()->create(['club_id' => $clube->id, 'role' => 'instrutor']);
        $desbravador = Desbravador::factory()->forClube($clube->id)->create();

        $response = $this->actingAs($user)->get(route('desbravadores.especialidades', $desbravador));

        $response->assertStatus(200);
    }

    public function test_pode_salvar_especialidades()
    {
        $clube = Club::create(['nome' => 'Clube Teste', 'cidade' => 'SP']);
        $user = User::factory()->create(['club_id' => $clube->id, 'role' => 'instrutor']);
        $desbravador = Desbravador::factory()->forClube($clube->id)->create();
        $especialidade = Especialidade::factory()->create();

        $response = $this->actingAs($user)->post(route('desbravadores.salvar-especialidades', $desbravador), [
            'especialidades' => [$especialidade->id],
            'data_conclusao' => '2025-06-01',
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('desbravador_especialidade', [
            'desbravador_id' => $desbravador->id,
            'especialidade_id' => $especialidade->id,
            'data_conclusao' => '2025-06-01',
        ]);
    }

    public function test_pode_remover_especialidade()
    {
        $clube = Club::create(['nome' => 'Clube Teste', 'cidade' => 'SP']);
        $user = User::factory()->create(['club_id' => $clube->id, 'role' => 'instrutor']);
        $desbravador = Desbravador::factory()->forClube($clube->id)->create();
        $especialidade = Especialidade::factory()->create();
        $desbravador->especialidades()->attach($especialidade->id, ['data_conclusao' => '2025-01-01']);

        $response = $this->actingAs($user)->delete(route('desbravadores.remover-especialidade', [$desbravador, $especialidade->id]));

        $response->assertRedirect();
        $this->assertDatabaseMissing('desbravador_especialidade', [
            'desbravador_id' => $desbravador->id,
            'especialidade_id' => $especialidade->id,
        ]);
    }

    public function test_pode_avancar_para_proxima_classe()
    {
        $clube = Club::create(['nome' => 'Clube Teste', 'cidade' => 'SP']);
        $user = User::factory()->create(['club_id' => $clube->id, 'role' => 'secretario']);
        $classeAtual = Classe::factory()->create(['ordem' => 1]);
        $proximaClasse = Classe::factory()->create(['ordem' => 2]);
        $desbravador = Desbravador::factory()->forClube($clube->id)->create(['classe_atual' => $classeAtual->id]);

        $response = $this->actingAs($user)->post(route('desbravadores.avancar-classe', $desbravador));

        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertEquals($proximaClasse->id, $desbravador->fresh()->classe_atual);
    }

    public function test_nao_pode_avancar_se_ja_na_maior_classe()
    {
        $clube = Club::create(['nome' => 'Clube Teste', 'cidade' => 'SP']);
        $user = User::factory()->create(['club_id' => $clube->id, 'role' => 'secretario']);
        $ultimaClasse = Classe::factory()->create(['ordem' => 99]);
        $desbravador = Desbravador::factory()->forClube($clube->id)->create(['classe_atual' => $ultimaClasse->id]);

        $response = $this->actingAs($user)->post(route('desbravadores.avancar-classe', $desbravador));

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertEquals($ultimaClasse->id, $desbravador->fresh()->classe_atual);
    }
}

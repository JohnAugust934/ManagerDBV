<?php

namespace Tests\Feature\MultiTenant;

use App\Models\Caixa;
use App\Models\User;
use App\Services\ClubContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SuperAdminImpersonacaoTest extends TestCase
{
    use RefreshDatabase;

    public function test_impersonacao_alterna_a_visao_entre_clubes(): void
    {
        ['club' => $clubA] = criarClubeComDados('Clube A');
        ['club' => $clubB] = criarClubeComDados('Clube B');

        Caixa::factory()->forClube($clubA->id)->create(['descricao' => 'Caixa A']);
        Caixa::factory()->forClube($clubB->id)->create(['descricao' => 'Caixa B']);

        $admin = User::factory()->platformAdmin()->create();
        $this->actingAs($admin);

        // 1. Entra no clube A → vê apenas dados do A
        $this->post(route('platform.enter', $clubA));
        $this->assertSame(['Caixa A'], Caixa::pluck('descricao')->all());

        // 2. Sai → vê todos os dados novamente
        $this->post(route('platform.exit'));
        $this->assertEqualsCanonicalizing(['Caixa A', 'Caixa B'], Caixa::pluck('descricao')->all());

        // 3. Entra no clube B → vê apenas dados do B (A não aparece)
        $this->post(route('platform.enter', $clubB));
        $this->assertSame(['Caixa B'], Caixa::pluck('descricao')->all());
        $this->assertSame($clubB->id, ClubContext::currentClubId());
    }

    public function test_modo_suporte_permite_criar_desbravador_no_clube_atendido(): void
    {
        ['club' => $clubA, 'unidade' => $unidadeA] = criarClubeComDados('Clube A');
        ['unidade' => $unidadeB] = criarClubeComDados('Clube B');

        $admin = User::factory()->platformAdmin()->create(['club_id' => null]);
        $this->actingAs($admin);
        $this->post(route('platform.enter', $clubA));

        $base = [
            'nome' => 'Fulano',
            'data_nascimento' => '2014-01-01',
            'sexo' => 'M',
            'cpf' => '123.456.789-00',
            'rg' => '11.111.111-1',
            'email' => 'fulano@ex.com',
            'endereco' => 'Rua X',
            'nome_responsavel' => 'Responsável',
            'telefone_responsavel' => '99999-9999',
            'numero_sus' => '12345678901',
        ];

        // Unidade do clube atendido → a regra UnidadePertenceAoClube usa ClubContext e passa.
        $this->post(route('desbravadores.store'), [...$base, 'unidade_id' => $unidadeA->id])
            ->assertRedirect(route('desbravadores.index'));
        $this->assertDatabaseHas('desbravadores', ['nome' => 'Fulano', 'club_id' => $clubA->id]);

        // Unidade de outro clube → barrada.
        $this->post(route('desbravadores.store'), [
            ...$base,
            'nome' => 'Cicrano',
            'cpf' => '987.654.321-00',
            'email' => 'cicrano@ex.com',
            'unidade_id' => $unidadeB->id,
        ])->assertSessionHasErrors('unidade_id');

        $this->assertDatabaseMissing('desbravadores', ['nome' => 'Cicrano']);
    }

    public function test_usuario_comum_nao_consegue_impersonar(): void
    {
        ['club' => $clubA, 'master' => $masterA] = criarClubeComDados('Clube A');
        ['club' => $clubB] = criarClubeComDados('Clube B');

        // Master de clube não passa no gate platform-admin.
        $this->actingAs($masterA)->post(route('platform.enter', $clubB))->assertForbidden();
    }
}

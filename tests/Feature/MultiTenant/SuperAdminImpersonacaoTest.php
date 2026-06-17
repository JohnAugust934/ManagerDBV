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

    public function test_usuario_comum_nao_consegue_impersonar(): void
    {
        ['club' => $clubA, 'master' => $masterA] = criarClubeComDados('Clube A');
        ['club' => $clubB] = criarClubeComDados('Clube B');

        // Master de clube não passa no gate platform-admin.
        $this->actingAs($masterA)->post(route('platform.enter', $clubB))->assertForbidden();
    }
}

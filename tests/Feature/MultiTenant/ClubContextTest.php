<?php

namespace Tests\Feature\MultiTenant;

use App\Models\Caixa;
use App\Models\Club;
use App\Models\User;
use App\Services\ClubContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClubContextTest extends TestCase
{
    use RefreshDatabase;

    private Club $clubA;

    private Club $clubB;

    protected function setUp(): void
    {
        parent::setUp();

        $this->clubA = Club::create(['nome' => 'Clube A', 'cidade' => 'SP']);
        $this->clubB = Club::create(['nome' => 'Clube B', 'cidade' => 'RJ']);

        Caixa::create(['descricao' => 'Caixa A', 'valor' => 10, 'tipo' => 'entrada', 'data_movimentacao' => now(), 'categoria' => 'x', 'club_id' => $this->clubA->id]);
        Caixa::create(['descricao' => 'Caixa B', 'valor' => 20, 'tipo' => 'entrada', 'data_movimentacao' => now(), 'categoria' => 'x', 'club_id' => $this->clubB->id]);
    }

    public function test_usuario_comum_ve_apenas_o_proprio_clube(): void
    {
        $user = User::factory()->create(['club_id' => $this->clubA->id, 'role' => 'diretor']);

        $this->actingAs($user);

        $this->assertSame($this->clubA->id, ClubContext::currentClubId());
        $this->assertFalse(ClubContext::isPlatformAdmin());
        $this->assertSame(['Caixa A'], Caixa::pluck('descricao')->all());
    }

    public function test_platform_admin_sem_contexto_ve_todos_os_clubes(): void
    {
        $admin = User::factory()->platformAdmin()->create();

        $this->actingAs($admin);

        $this->assertNull(ClubContext::currentClubId());
        $this->assertTrue(ClubContext::isPlatformAdmin());
        $this->assertFalse(ClubContext::isImpersonating());
        $this->assertEqualsCanonicalizing(['Caixa A', 'Caixa B'], Caixa::pluck('descricao')->all());
    }

    public function test_platform_admin_impersonando_ve_apenas_o_clube_da_sessao(): void
    {
        $admin = User::factory()->platformAdmin()->create();

        $this->actingAs($admin);
        session([ClubContext::SESSION_KEY => $this->clubB->id]);

        $this->assertSame($this->clubB->id, ClubContext::currentClubId());
        $this->assertTrue(ClubContext::isImpersonating());
        $this->assertSame(['Caixa B'], Caixa::pluck('descricao')->all());
    }

    public function test_usuario_comum_sem_clube_nao_ve_nada(): void
    {
        $user = User::factory()->create(['club_id' => null, 'role' => 'diretor']);

        $this->actingAs($user);

        $this->assertCount(0, Caixa::all());
    }
}

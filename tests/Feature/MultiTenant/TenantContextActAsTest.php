<?php

namespace Tests\Feature\MultiTenant;

use App\Models\Caixa;
use App\Services\ClubContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;
use Tests\TestCase;

/**
 * Fase 5 — contexto de tenant fora do HTTP. `ClubContext::actAs` permite que
 * jobs/comandos/seeders rodem dentro de um tenant declarado, e os global scopes
 * passam a filtrar por ele mesmo sem `auth()`.
 */
class TenantContextActAsTest extends TestCase
{
    use RefreshDatabase;

    public function test_act_as_filtra_scopes_sem_autenticacao(): void
    {
        ['club' => $clubA] = criarClubeComDados('Clube A');
        ['club' => $clubB] = criarClubeComDados('Clube B');

        Caixa::factory()->forClube($clubA->id)->create();
        Caixa::factory()->forClube($clubB->id)->create();

        // Sem auth e sem actAs (seeders/console): vê tudo.
        $this->assertSame(2, Caixa::count());

        // Dentro de actAs($clubA): só o A.
        $this->assertSame(1, ClubContext::actAs($clubA->id, fn () => Caixa::count()));
        $this->assertSame(1, ClubContext::actAs($clubB->id, fn () => Caixa::count()));

        // Fora do actAs: volta a ver tudo (contexto restaurado).
        $this->assertSame(2, Caixa::count());
        $this->assertFalse(ClubContext::hasOverride());
    }

    public function test_act_as_preenche_club_id_ao_criar(): void
    {
        ['club' => $clubA] = criarClubeComDados('Clube A');

        // Simula um job criando um registro sem informar club_id.
        $caixa = ClubContext::actAs($clubA->id, fn () => Caixa::create([
            'descricao' => 'Lançamento de job',
            'valor' => 10,
            'tipo' => 'entrada',
            'data_movimentacao' => now()->toDateString(),
            'categoria' => 'x',
        ]));

        $this->assertSame($clubA->id, $caixa->club_id);
    }

    public function test_act_as_e_aninhavel_e_restaura(): void
    {
        ['club' => $clubA] = criarClubeComDados('Clube A');
        ['club' => $clubB] = criarClubeComDados('Clube B');

        ClubContext::actAs($clubA->id, function () use ($clubA, $clubB) {
            $this->assertSame($clubA->id, ClubContext::currentClubId());

            ClubContext::actAs($clubB->id, function () use ($clubB) {
                $this->assertSame($clubB->id, ClubContext::currentClubId());
            });

            // Restaurado ao A após o aninhamento.
            $this->assertSame($clubA->id, ClubContext::currentClubId());
        });

        $this->assertFalse(ClubContext::hasOverride());
        $this->assertNull(ClubContext::currentClubId());
    }

    public function test_act_as_restaura_mesmo_com_excecao(): void
    {
        ['club' => $clubA] = criarClubeComDados('Clube A');

        try {
            ClubContext::actAs($clubA->id, function () {
                throw new RuntimeException('boom');
            });
        } catch (RuntimeException) {
            // esperado
        }

        $this->assertFalse(ClubContext::hasOverride());
        $this->assertNull(ClubContext::currentClubId());
    }
}

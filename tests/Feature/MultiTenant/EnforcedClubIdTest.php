<?php

namespace Tests\Feature\MultiTenant;

use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Fase 2 — integridade referencial. Garante que club_id é NOT NULL nas tabelas
 * de tenant estritas e que invitations continua aceitando nulo (bootstrap).
 *
 * Nota: a FK com ON DELETE CASCADE só existe em MySQL/Postgres (dev/produção);
 * no SQLite (testes) ela é pulada, então o cascade não é exercido aqui — a rede
 * agnóstica é o `tenant:check-integrity`.
 */
class EnforcedClubIdTest extends TestCase
{
    use RefreshDatabase;

    public function test_club_id_nulo_e_rejeitado_nas_tabelas_estritas(): void
    {
        $casos = [
            'caixas' => ['descricao' => 'X', 'valor' => 1, 'tipo' => 'entrada', 'data_movimentacao' => '2026-01-01', 'categoria' => 'x'],
            'eventos' => ['nome' => 'X', 'data_inicio' => '2026-01-01 09:00:00', 'local' => 'x', 'valor' => 0],
            'atos' => ['descricao' => 'X'],
        ];

        foreach ($casos as $tabela => $row) {
            $rejeitou = false;
            try {
                DB::table($tabela)->insert([...$row, 'club_id' => null, 'created_at' => now(), 'updated_at' => now()]);
            } catch (QueryException) {
                $rejeitou = true;
            }

            $this->assertTrue($rejeitou, "Esperava NOT NULL em {$tabela}.club_id, mas o insert com nulo passou.");
        }
    }

    public function test_invitation_aceita_club_id_nulo(): void
    {
        DB::table('invitations')->insert([
            'email' => 'boot@clube.com',
            'token' => 'tok-boot',
            'role' => 'diretor',
            'club_id' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->assertDatabaseHas('invitations', ['email' => 'boot@clube.com', 'club_id' => null]);
    }
}

<?php

namespace Tests\Feature;

use App\Models\Club;
use App\Models\Unidade;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ResilienciaOperacionalTest extends TestCase
{
    use RefreshDatabase;

    public function test_health_check_retorna_200_com_banco_saudavel(): void
    {
        $response = $this->get('/health');

        $response->assertOk();
        $response->assertJsonPath('database', 'ok');
        $response->assertJsonStructure([
            'status', 'database', 'cache', 'queue_size', 'failed_jobs_24h', 'timestamp',
        ]);
    }

    public function test_health_check_inclui_tamanho_da_fila(): void
    {
        $response = $this->get('/health');

        $response->assertOk();
        $this->assertIsInt($response->json('queue_size'));
        $this->assertGreaterThanOrEqual(0, $response->json('queue_size'));
    }

    public function test_health_check_status_ok_quando_fila_pequena(): void
    {
        $response = $this->get('/health');

        // Com fila vazia (ambiente de testes), o status deve ser ok
        $response->assertOk();
        $this->assertSame('ok', $response->json('status'));
    }

    public function test_retry_on_deadlock_executa_callback_com_sucesso(): void
    {
        $clube = Club::create(['nome' => 'Clube Retry', 'cidade' => 'SP']);

        $resultado = DB::retryOnDeadlock(function () use ($clube) {
            return Unidade::create([
                'nome' => 'Unidade Retry',
                'club_id' => $clube->id,
            ]);
        });

        $this->assertInstanceOf(Unidade::class, $resultado);
        $this->assertDatabaseHas('unidades', ['nome' => 'Unidade Retry']);
    }

    public function test_retry_on_deadlock_propaga_excecao_nao_deadlock(): void
    {
        $this->expectException(\Illuminate\Database\QueryException::class);

        DB::retryOnDeadlock(function () {
            // Viola constraint de chave estrangeira (não é deadlock)
            DB::table('unidades')->insert([
                'nome' => 'Unidade Inválida',
                'club_id' => 99999, // club_id inexistente
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        });
    }

    public function test_caixa_store_usa_retry_on_deadlock(): void
    {
        $clube = Club::create(['nome' => 'Clube Caixa', 'cidade' => 'SP']);
        $user = User::factory()->create(['club_id' => $clube->id, 'role' => 'tesoureiro']);

        $this->actingAs($user)
            ->post(route('caixa.store'), [
                'descricao' => 'Entrada Teste',
                'valor' => 150.00,
                'tipo' => 'entrada',
                'data_movimentacao' => now()->toDateString(),
                'categoria' => 'Testes',
            ])
            ->assertRedirect(route('caixa.index'));

        $this->assertDatabaseHas('caixas', [
            'descricao' => 'Entrada Teste',
            'club_id' => $clube->id,
        ]);
    }
}

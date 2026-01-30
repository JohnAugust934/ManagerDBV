<?php

namespace Tests\Feature;

use App\Models\Club;
use App\Models\Desbravador;
use App\Models\Unidade;
use App\Models\User;
use App\Models\Caixa;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RelatorioTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        $clube = Club::create(['nome' => 'Clube Teste', 'cidade' => 'SP']);
        $this->user = User::factory()->create(['club_id' => $clube->id]);
        $unidade = Unidade::factory()->create();
        Desbravador::factory()->create(['unidade_id' => $unidade->id, 'ativo' => true]);
    }

    public function test_pode_acessar_central_de_relatorios()
    {
        $response = $this->actingAs($this->user)->get(route('relatorios.index'));
        $response->assertStatus(200);
        $response->assertSee('Gerador de Relatório Personalizado');
    }

    public function test_pode_gerar_relatorio_personalizado_desbravadores()
    {
        $response = $this->actingAs($this->user)->post(route('relatorios.custom'), [
            'tipo' => 'desbravadores',
            'status' => 'ativos'
        ]);

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/pdf');
    }

    public function test_pode_gerar_relatorio_personalizado_caixa()
    {
        Caixa::create(['descricao' => 'Teste', 'valor' => 50, 'tipo' => 'entrada', 'data_movimentacao' => now()]);

        $response = $this->actingAs($this->user)->post(route('relatorios.custom'), [
            'tipo' => 'caixa',
            'tipo_movimentacao' => 'todos'
        ]);

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/pdf');
    }

    public function test_pode_gerar_fichas_medicas_em_lote()
    {
        $response = $this->actingAs($this->user)->post(route('relatorios.custom'), [
            'tipo' => 'fichas_medicas',
            'status' => 'ativos'
        ]);

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/pdf');
    }
}

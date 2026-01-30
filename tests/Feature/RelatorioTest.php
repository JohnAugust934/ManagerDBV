<?php

namespace Tests\Feature;

use App\Models\Club;
use App\Models\Desbravador;
use App\Models\Unidade;
use App\Models\User;
use App\Models\Caixa;
use App\Models\Patrimonio;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RelatorioTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $desbravador;

    protected function setUp(): void
    {
        parent::setUp();
        $clube = Club::create(['nome' => 'Clube Teste', 'cidade' => 'SP']);
        $this->user = User::factory()->create(['club_id' => $clube->id]);
        $unidade = Unidade::factory()->create();
        $this->desbravador = Desbravador::factory()->create([
            'unidade_id' => $unidade->id,
            'nome_responsavel' => 'Responsavel Teste',
            'numero_sus' => '123456789'
        ]);
    }

    public function test_pode_gerar_pdf_de_autorizacao()
    {
        // Verifica se a rota existe e retorna 200
        $response = $this->actingAs($this->user)
            ->get(route('relatorios.autorizacao', $this->desbravador->id));

        $response->assertStatus(200);
        // Verifica se o header é de PDF (independente se é download ou stream)
        $response->assertHeader('content-type', 'application/pdf');
    }

    public function test_pode_gerar_carteirinha()
    {
        $response = $this->actingAs($this->user)
            ->get(route('relatorios.carteirinha', $this->desbravador->id));

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/pdf');
    }

    public function test_pode_gerar_ficha_medica()
    {
        $response = $this->actingAs($this->user)
            ->get(route('relatorios.ficha-medica', $this->desbravador->id));

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/pdf');
    }

    public function test_pode_gerar_relatorio_financeiro()
    {
        Caixa::create(['descricao' => 'Teste', 'tipo' => 'entrada', 'valor' => 100, 'data_movimentacao' => now()]);

        $response = $this->actingAs($this->user)
            ->get(route('relatorios.financeiro'));

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/pdf');
    }

    public function test_pode_gerar_relatorio_de_patrimonio()
    {
        Patrimonio::factory()->create();

        $response = $this->actingAs($this->user)
            ->get(route('relatorios.patrimonio'));

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/pdf');
    }
}

<?php

namespace Tests\Feature;

use App\Models\Caixa;
use App\Models\Club;
use App\Models\Desbravador;
use App\Models\Unidade;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RelatorioTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected $clube;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Criamos o Clube
        $this->clube = Club::create(['nome' => 'Clube Teste', 'cidade' => 'SP']);

        // CORREÇÃO FINAL: Usamos o cargo 'diretor' que possui explicitamente as permissões de 'relatorios' e 'financeiro'
        $this->user = User::factory()->create([
            'club_id' => $this->clube->id,
            'role' => 'diretor',
        ]);

        // 3. Criamos a unidade e desbravador atrelados
        $unidade = Unidade::factory()->create(['club_id' => $this->clube->id]);

        Desbravador::factory()->create([
            'unidade_id' => $unidade->id,
            'ativo' => true,
        ]);
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
            'status' => 'ativos',
        ]);

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/pdf');
    }

    public function test_pode_gerar_relatorio_personalizado_caixa()
    {
        Caixa::create([
            'descricao' => 'Teste',
            'valor' => 50,
            'tipo' => 'entrada',
            'data_movimentacao' => now(),
            'club_id' => $this->clube->id,
        ]);

        $response = $this->actingAs($this->user)->post(route('relatorios.custom'), [
            'tipo' => 'caixa',
            'tipo_movimentacao' => 'todos',
        ]);

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/pdf');
    }

    public function test_pode_gerar_fichas_medicas_em_lote()
    {
        $response = $this->actingAs($this->user)->post(route('relatorios.custom'), [
            'tipo' => 'fichas_medicas',
            'status' => 'ativos',
        ]);

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/pdf');
    }
}

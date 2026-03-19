<?php

namespace Tests\Feature;

use App\Models\Classe;
use App\Models\Club;
use App\Models\Desbravador;
use App\Models\Mensalidade;
use App\Models\Unidade;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FluxoOperacionalCentralTest extends TestCase
{
    use RefreshDatabase;

    public function test_fluxo_de_cadastro_inativacao_mensalidade_e_caixa_permanece_consistente()
    {
        $clube = Club::create(['nome' => 'Clube Central', 'cidade' => 'SP']);
        $secretario = User::factory()->create(['club_id' => $clube->id, 'role' => 'secretario']);
        $tesoureiro = User::factory()->create(['club_id' => $clube->id, 'role' => 'tesoureiro']);
        $unidade = Unidade::factory()->create(['club_id' => $clube->id]);
        $classe = Classe::factory()->create();

        $this->actingAs($secretario)->post(route('desbravadores.store'), [
            'nome' => 'Ativo Operacional',
            'data_nascimento' => '2010-01-01',
            'sexo' => 'M',
            'cpf' => '111.222.333-44',
            'rg' => '55.666.777-8',
            'unidade_id' => $unidade->id,
            'classe_atual' => $classe->id,
            'email' => 'ativo@teste.com',
            'nome_responsavel' => 'Responsavel Ativo',
            'telefone_responsavel' => '11999999999',
            'numero_sus' => '12345678900',
            'endereco' => 'Rua A, 10',
        ])->assertRedirect(route('desbravadores.index'));

        $inativo = Desbravador::factory()->create([
            'unidade_id' => $unidade->id,
            'classe_atual' => $classe->id,
            'ativo' => false,
        ]);

        $ativo = Desbravador::where('cpf', '111.222.333-44')->firstOrFail();

        $this->actingAs($tesoureiro)->post(route('mensalidades.gerar'), [
            'mes' => now()->month,
            'ano' => now()->year,
            'valor' => 45.50,
        ])->assertRedirect();

        $this->assertDatabaseHas('mensalidades', [
            'desbravador_id' => $ativo->id,
            'status' => 'pendente',
        ]);
        $this->assertDatabaseMissing('mensalidades', [
            'desbravador_id' => $inativo->id,
            'mes' => now()->month,
            'ano' => now()->year,
        ]);

        $mensalidadeId = Mensalidade::where('desbravador_id', $ativo->id)->value('id');
        $this->actingAs($tesoureiro)->post(route('mensalidades.pagar', $mensalidadeId))->assertRedirect();

        $this->assertDatabaseHas('caixas', [
            'categoria' => 'Mensalidade',
            'tipo' => 'entrada',
        ]);

        $this->actingAs($secretario)->put(route('desbravadores.update', $ativo), [
            'nome' => $ativo->nome,
            'data_nascimento' => $ativo->data_nascimento->format('Y-m-d'),
            'sexo' => $ativo->sexo,
            'cpf' => $ativo->cpf,
            'rg' => $ativo->rg,
            'unidade_id' => $ativo->unidade_id,
            'classe_atual' => $ativo->classe_atual,
            'email' => $ativo->email,
            'telefone' => $ativo->telefone,
            'endereco' => $ativo->endereco,
            'nome_responsavel' => $ativo->nome_responsavel,
            'telefone_responsavel' => $ativo->telefone_responsavel,
            'numero_sus' => $ativo->numero_sus,
        ])->assertRedirect(route('desbravadores.show', $ativo));

        $this->assertDatabaseHas('desbravadores', [
            'id' => $ativo->id,
            'ativo' => false,
        ]);
    }
}

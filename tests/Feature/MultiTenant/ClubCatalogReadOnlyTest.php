<?php

namespace Tests\Feature\MultiTenant;

use App\Models\Desbravador;
use App\Models\Especialidade;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Fase 6 — catálogo global read-only para clubes. O catálogo (especialidades,
 * requisitos) é compartilhado entre todos os clubes; só o platform admin edita.
 * Clubes apenas consomem (leitura) e registram o progresso dos seus desbravadores.
 */
class ClubCatalogReadOnlyTest extends TestCase
{
    use RefreshDatabase;

    public function test_clube_le_o_catalogo(): void
    {
        ['master' => $master] = criarClubeComDados('Clube A');

        $this->actingAs($master)->get(route('especialidades.index'))->assertOk();
    }

    public function test_clube_nao_cria_nem_edita_especialidade(): void
    {
        ['master' => $master] = criarClubeComDados('Clube A');
        $esp = Especialidade::factory()->create();

        $this->actingAs($master)->get(route('especialidades.create'))->assertForbidden();
        $this->actingAs($master)->post(route('especialidades.store'), ['nome' => 'X', 'area' => 'Y'])->assertForbidden();
        $this->actingAs($master)->get(route('especialidades.edit', $esp))->assertForbidden();
        $this->actingAs($master)->delete(route('especialidades.destroy', $esp))->assertForbidden();

        $this->assertDatabaseHas('especialidades', ['id' => $esp->id]);
    }

    public function test_clube_nao_edita_requisitos_de_classe(): void
    {
        ['master' => $master] = criarClubeComDados('Clube A');
        $classe = \App\Models\Classe::factory()->create();

        $this->actingAs($master)->post(route('classes.requisitos.store', $classe), [
            'descricao' => 'Requisito injetado',
        ])->assertForbidden();

        $this->assertDatabaseMissing('requisitos', ['descricao' => 'Requisito injetado']);
    }

    public function test_clube_ainda_registra_progresso_do_desbravador(): void
    {
        ['master' => $master, 'unidade' => $unidade] = criarClubeComDados('Clube A');
        $dbv = Desbravador::factory()->create(['unidade_id' => $unidade->id]);
        $esp = Especialidade::factory()->create();

        $this->actingAs($master)->post(route('desbravadores.salvar-especialidades', $dbv), [
            'especialidades' => [$esp->id],
            'data_conclusao' => now()->toDateString(),
        ])->assertRedirect();

        $this->assertDatabaseHas('desbravador_especialidade', [
            'desbravador_id' => $dbv->id,
            'especialidade_id' => $esp->id,
        ]);
    }

    public function test_platform_admin_edita_catalogo_em_modo_suporte(): void
    {
        $admin = User::factory()->platformAdmin()->create(['club_id' => null]);
        ['club' => $club] = criarClubeComDados('Clube A');

        $this->actingAs($admin);
        $this->post(route('platform.enter', $club)); // modo suporte

        // Passa pelo gate platform-admin — acessa o formulário de catálogo.
        $this->get(route('especialidades.create'))->assertOk();
    }
}

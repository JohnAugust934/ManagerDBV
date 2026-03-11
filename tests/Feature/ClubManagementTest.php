<?php

namespace Tests\Feature;

use App\Models\Club;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ClubManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_diretor_pode_visualizar_pagina_de_edicao_do_clube()
    {
        $club = Club::create(['nome' => 'Clube Teste', 'cidade' => 'SP', 'associacao' => 'APaC']);
        $user = User::factory()->create(['role' => 'diretor', 'club_id' => $club->id]);

        $response = $this->actingAs($user)->get(route('club.edit'));

        $response->assertStatus(200);
        $response->assertSee('Configurações do Clube');
        $response->assertSee('Identidade Visual');
        $response->assertSee('Dados Cadastrais');
    }

    public function test_diretor_pode_atualizar_informacoes_basicas()
    {
        $club = Club::create(['nome' => 'Clube Velho', 'cidade' => 'SP', 'associacao' => 'APaC']);
        $user = User::factory()->create(['role' => 'diretor', 'club_id' => $club->id]);

        $response = $this->actingAs($user)->patch(route('club.update'), [
            'nome' => 'Novo Nome',
            'cidade' => 'Nova Cidade',
            'associacao' => 'Nova Assoc',
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect();

        $this->assertDatabaseHas('clubs', [
            'id' => $club->id,
            'nome' => 'Novo Nome',
            'cidade' => 'Nova Cidade',
            'associacao' => 'Nova Assoc',
        ]);
    }

    public function test_validacao_de_campos_obrigatorios_funciona()
    {
        $club = Club::create(['nome' => 'Clube Teste', 'cidade' => 'SP', 'associacao' => 'APaC']);
        $user = User::factory()->create(['role' => 'diretor', 'club_id' => $club->id]);

        $response = $this->actingAs($user)->patch(route('club.update'), [
            'nome' => '',
            'cidade' => '',
            'associacao' => '',
        ]);

        $response->assertSessionHasErrors(['nome', 'cidade', 'associacao']);
    }

    public function test_upload_e_remocao_de_brasao()
    {
        Storage::fake('public');

        $club = Club::create(['nome' => 'Clube Teste', 'cidade' => 'SP', 'associacao' => 'APaC']);
        $user = User::factory()->create(['role' => 'diretor', 'club_id' => $club->id]);

        // 1. Teste de Upload
        $file = UploadedFile::fake()->image('logo.jpg');

        $this->actingAs($user)->patch(route('club.update'), [
            'nome' => 'Clube Teste',
            'cidade' => 'SP',
            'associacao' => 'APaC',
            'logo' => $file,
        ]);

        $club->refresh();
        $this->assertNotNull($club->logo);
        Storage::disk('public')->assertExists($club->logo);

        // 2. Teste de Remoção
        $this->actingAs($user)->delete(route('club.remove_logo'));

        $club->refresh();
        $this->assertNull($club->logo);
        Storage::disk('public')->assertMissing('logos/logo.jpg');
    }
}

<?php

use App\Models\Ato;
use App\Models\User;

test('usuario pode ver lista de atos', function () {
    $user = User::factory()->create();
    Ato::factory()->count(2)->create();

    $response = $this->actingAs($user)->get(route('atos.index'));
    $response->assertStatus(200);
});

test('usuario pode registrar um ato administrativo', function () {
    $user = User::factory()->create();
    $dados = [
        'data' => '2025-05-20',
        'tipo' => 'Nomeação',
        'descricao_resumida' => 'Nomeação de Conselheiro',
    ];

    $response = $this->actingAs($user)->post(route('atos.store'), $dados);

    $response->assertRedirect(route('atos.index'));
    $this->assertDatabaseHas('atos', ['descricao_resumida' => 'Nomeação de Conselheiro']);
});

<?php

use App\Models\Patrimonio;
use App\Models\User;

test('usuario pode ver lista de patrimonio', function () {
    $user = User::factory()->create();
    Patrimonio::factory()->count(3)->create();

    $response = $this->actingAs($user)->get(route('patrimonio.index'));
    $response->assertStatus(200);
});

test('usuario pode cadastrar novo item', function () {
    $user = User::factory()->create();
    $dados = [
        'item' => 'Barraca Teste',
        'quantidade' => 2,
        'estado_conservacao' => 'Novo',
        'valor_estimado' => 500.00
    ];

    $response = $this->actingAs($user)->post(route('patrimonio.store'), $dados);

    $response->assertRedirect(route('patrimonio.index'));
    $this->assertDatabaseHas('patrimonios', ['item' => 'Barraca Teste']);
});

<?php

use App\Models\User;
use App\Models\Desbravador;
use App\Models\Caixa;
use App\Models\Patrimonio;

test('pode gerar pdf de autorizacao', function () {
    $user = User::factory()->create();
    $dbv = Desbravador::factory()->create();

    $response = $this->actingAs($user)->get(route('relatorios.autorizacao', $dbv->id));

    $response->assertStatus(200);
    // Verifica se o cabeçalho é de PDF
    $response->assertHeader('content-type', 'application/pdf');
});

test('pode gerar relatorio financeiro', function () {
    $user = User::factory()->create();
    Caixa::factory()->count(5)->create();

    $response = $this->actingAs($user)->get(route('relatorios.financeiro'));

    $response->assertStatus(200);
    $response->assertHeader('content-type', 'application/pdf');
});

test('pode gerar relatorio de patrimonio', function () {
    $user = User::factory()->create();
    Patrimonio::factory()->count(3)->create();

    $response = $this->actingAs($user)->get(route('relatorios.patrimonio'));

    $response->assertStatus(200);
    $response->assertHeader('content-type', 'application/pdf');
});

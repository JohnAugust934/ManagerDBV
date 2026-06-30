<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Páginas legais públicas (política de privacidade e termos de uso) — renderizam
 * sem autenticação e exibem o conteúdo esperado.
 */
class LegalTest extends TestCase
{
    use RefreshDatabase;

    public function test_pagina_de_privacidade_renderiza(): void
    {
        $this->get(route('legal.privacidade'))
            ->assertOk()
            ->assertSee('Privacidade', false);
    }

    public function test_pagina_de_termos_renderiza(): void
    {
        $this->get(route('legal.termos'))
            ->assertOk()
            ->assertSee('Termos', false);
    }
}

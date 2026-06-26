<?php

namespace Tests\Feature;

use App\Models\Club;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SecurityHeadersTest extends TestCase
{
    use RefreshDatabase;

    public function test_headers_de_seguranca_presentes_em_paginas_autenticadas(): void
    {
        $clube = Club::create(['nome' => 'Clube Teste', 'cidade' => 'SP']);
        $user = User::factory()->create(['club_id' => $clube->id, 'role' => 'diretor']);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('X-Frame-Options', 'SAMEORIGIN');
        $response->assertHeader('X-XSS-Protection', '1; mode=block');
        $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->assertHeader('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');
    }

    public function test_headers_de_seguranca_presentes_em_paginas_publicas(): void
    {
        $response = $this->get('/');

        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('X-Frame-Options', 'SAMEORIGIN');
    }

    public function test_csp_header_presente(): void
    {
        $clube = Club::create(['nome' => 'Clube CSP', 'cidade' => 'SP']);
        $user = User::factory()->create(['club_id' => $clube->id, 'role' => 'diretor']);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $csp = $response->headers->get('Content-Security-Policy');
        $this->assertNotNull($csp, 'Content-Security-Policy header deve estar presente');
        $this->assertStringContainsString("default-src 'self'", $csp);
        $this->assertStringContainsString("frame-ancestors 'none'", $csp);
    }

    public function test_hsts_nao_enviado_fora_de_producao(): void
    {
        // Em ambiente de teste (não produção), HSTS não deve ser enviado
        $clube = Club::create(['nome' => 'Clube HSTS', 'cidade' => 'SP']);
        $user = User::factory()->create(['club_id' => $clube->id, 'role' => 'diretor']);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $this->assertNull(
            $response->headers->get('Strict-Transport-Security'),
            'HSTS não deve ser enviado em ambiente não-produção',
        );
    }

    public function test_rate_limiting_em_relatorios(): void
    {
        $clube = Club::create(['nome' => 'Clube RL', 'cidade' => 'SP']);
        $user = User::factory()->create(['club_id' => $clube->id, 'role' => 'diretor']);

        // Faz 10 requests (limite por minuto)
        for ($i = 0; $i < 10; $i++) {
            $this->actingAs($user)->post(route('relatorios.custom'), [
                'tipo' => 'desbravadores',
                'status' => 'ativos',
            ]);
        }

        // O 11º deve ser bloqueado (429 Too Many Requests)
        $response = $this->actingAs($user)->post(route('relatorios.custom'), [
            'tipo' => 'desbravadores',
            'status' => 'ativos',
        ]);

        $response->assertStatus(429);
    }
}

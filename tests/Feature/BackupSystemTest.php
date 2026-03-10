<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class BackupSystemTest extends TestCase
{
    use RefreshDatabase;

    public function test_apenas_master_pode_acessar_backups()
    {
        $master = User::factory()->create(['role' => 'master']);
        $diretor = User::factory()->create(['role' => 'diretor']);

        // Diretor tenta acessar e toma bloqueio
        $this->actingAs($diretor)->get(route('backups.index'))->assertForbidden();

        // Master acessa com sucesso
        $this->actingAs($master)->get(route('backups.index'))->assertOk();
    }

    public function test_master_pode_gerar_backup()
    {
        // Impede que o teste de fato tente rodar o backup pesado e trave
        Artisan::spy();

        $master = User::factory()->create(['role' => 'master']);

        $response = $this->actingAs($master)->post(route('backups.store'));

        $response->assertRedirect();

        // Verifica se o comando artisan backup:run foi acionado com os parâmetros exatos do Controller
        Artisan::shouldHaveReceived('call')->with('backup:run', [
            '--disable-notifications' => true,
        ]);
    }
}

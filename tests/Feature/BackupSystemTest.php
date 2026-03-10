<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
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
        // Impede que o teste de fato tente rodar o backup pesado no banco e trave
        Artisan::spy();

        $master = User::factory()->create(['role' => 'master']);

        $response = $this->actingAs($master)->post(route('backups.store'));

        $response->assertRedirect();

        // Verifica se o comando artisan backup:run foi acionado com os parâmetros exatos do Controller
        Artisan::shouldHaveReceived('call')->with('backup:run', [
            '--disable-notifications' => true,
        ]);
    }

    public function test_master_pode_importar_backup_zip()
    {
        Storage::fake('local');
        $master = User::factory()->create(['role' => 'master']);

        // Simula o upload de um arquivo ZIP
        $file = UploadedFile::fake()->create('meu_backup_antigo.zip', 1024, 'application/zip');

        $response = $this->actingAs($master)->post(route('backups.import'), [
            'backup_file' => $file,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Verifica se o arquivo foi salvo fisicamente no disco fake
        $pasta = config('backup.backup.name', 'Laravel');
        Storage::disk('local')->assertExists($pasta.'/meu_backup_antigo.zip');
    }

    public function test_master_pode_acionar_restauracao_mas_falha_em_zip_falso()
    {
        Storage::fake('local');
        $master = User::factory()->create(['role' => 'master']);

        // Colocamos um arquivo de texto fingindo ser ZIP no storage para testar a blindagem
        $pasta = config('backup.backup.name', 'Laravel');
        Storage::disk('local')->put($pasta.'/fake.zip', 'nao sou um zip real');

        $response = $this->actingAs($master)->post(route('backups.restore'), [
            'disk' => 'local',
            'path' => $pasta.'/fake.zip',
        ]);

        $response->assertRedirect();
        // A lógica do controller vai tentar extrair o ZIP, falhar por ser arquivo corrompido e redirecionar com erro (segurança)
        $response->assertSessionHas('error');
    }

    public function test_master_pode_baixar_backup()
    {
        Storage::fake('local');
        $master = User::factory()->create(['role' => 'master']);

        // Cria um arquivo fictício para ser baixado
        $pasta = config('backup.backup.name', 'Laravel');
        $caminho = $pasta.'/meu_backup_para_download.zip';
        Storage::disk('local')->put($caminho, 'conteudo_zip_fake');

        $response = $this->actingAs($master)->get(route('backups.download', [
            'disk' => 'local',
            'path' => $caminho,
        ]));

        // Verifica se o sistema inicia o download do arquivo perfeitamente
        $response->assertDownload('meu_backup_para_download.zip');
    }

    public function test_master_pode_excluir_backup()
    {
        Storage::fake('local');
        $master = User::factory()->create(['role' => 'master']);

        // Cria um arquivo fictício para ser excluído
        $pasta = config('backup.backup.name', 'Laravel');
        $caminho = $pasta.'/backup_para_apagar.zip';
        Storage::disk('local')->put($caminho, 'conteudo_zip_fake');

        // Garante que ele existe antes da exclusão
        Storage::disk('local')->assertExists($caminho);

        $response = $this->actingAs($master)->delete(route('backups.destroy'), [
            'disk' => 'local',
            'path' => $caminho,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Garante que o arquivo sumiu do disco fisicamente
        Storage::disk('local')->assertMissing($caminho);
    }
}

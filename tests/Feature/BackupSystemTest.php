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
        // Finge que os discos existem para não quebrar no GitHub Actions (onde não há .env do R2)
        Storage::fake('local');
        Storage::fake('r2');

        $master = User::factory()->create(['role' => 'master']);
        $diretor = User::factory()->create(['role' => 'diretor']);

        $this->actingAs($diretor)->get(route('backups.index'))->assertForbidden();
        $this->actingAs($master)->get(route('backups.index'))->assertOk();
    }

    public function test_master_pode_gerar_backup()
    {
        Storage::fake('local');
        Storage::fake('r2');
        Artisan::spy();

        $master = User::factory()->create(['role' => 'master']);

        $response = $this->actingAs($master)->post(route('backups.store'));

        $response->assertRedirect();
        Artisan::shouldHaveReceived('call')->with('backup:run', [
            '--disable-notifications' => true,
        ]);
    }

    public function test_master_pode_importar_backup_zip()
    {
        Storage::fake('local');
        Storage::fake('r2');
        $master = User::factory()->create(['role' => 'master']);

        $file = UploadedFile::fake()->create('meu_backup_antigo.zip', 1024, 'application/zip');

        $response = $this->actingAs($master)->post(route('backups.import'), [
            'backup_file' => $file,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $pasta = config('backup.backup.name', 'Laravel');
        Storage::disk('local')->assertExists($pasta.'/meu_backup_antigo.zip');
    }

    public function test_master_pode_acionar_restauracao_com_modo_manutencao()
    {
        Storage::fake('local');
        Storage::fake('r2');
        Artisan::spy();

        $master = User::factory()->create(['role' => 'master']);

        $pasta = config('backup.backup.name', 'Laravel');
        Storage::disk('local')->put($pasta.'/fake.zip', 'nao sou um zip real');

        $response = $this->actingAs($master)->post(route('backups.restore'), [
            'disk' => 'local',
            'path' => $pasta.'/fake.zip',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');

        Artisan::shouldHaveReceived('call')->with('down');
        Artisan::shouldHaveReceived('call')->with('up');
    }

    public function test_master_pode_baixar_backup()
    {
        Storage::fake('local');
        Storage::fake('r2');
        $master = User::factory()->create(['role' => 'master']);

        $pasta = config('backup.backup.name', 'Laravel');
        $caminho = $pasta.'/meu_backup_para_download.zip';
        Storage::disk('local')->put($caminho, 'conteudo_zip_fake');

        $response = $this->actingAs($master)->get(route('backups.download', [
            'disk' => 'local',
            'path' => $caminho,
        ]));

        $response->assertDownload('meu_backup_para_download.zip');
    }

    public function test_master_pode_excluir_backup()
    {
        Storage::fake('local');
        Storage::fake('r2');
        $master = User::factory()->create(['role' => 'master']);

        $pasta = config('backup.backup.name', 'Laravel');
        $caminho = $pasta.'/backup_para_apagar.zip';
        Storage::disk('local')->put($caminho, 'conteudo_zip_fake');

        Storage::disk('local')->assertExists($caminho);

        $response = $this->actingAs($master)->delete(route('backups.destroy'), [
            'disk' => 'local',
            'path' => $caminho,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
        Storage::disk('local')->assertMissing($caminho);
    }
}

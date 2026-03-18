<?php

namespace Tests\Feature;

use App\Models\Club;
use App\Models\User;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use ZipArchive;

class BackupRestoreIntegrationTest extends TestCase
{
    private string $databasePath;

    private string $backupDatabasePath;

    private string $zipPath;

    private string $originalDefaultConnection;

    private string $originalSqliteDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->originalDefaultConnection = config('database.default');
        $this->originalSqliteDatabase = (string) config('database.connections.sqlite.database');
        $this->databasePath = storage_path('framework/testing/restore-flow.sqlite');
        $this->backupDatabasePath = storage_path('framework/testing/restore-flow-backup.sqlite');
        $this->zipPath = storage_path('framework/testing/restore-flow-valid.zip');

        File::ensureDirectoryExists(dirname($this->databasePath));
        @unlink($this->databasePath);
        @unlink($this->backupDatabasePath);
        @unlink($this->zipPath);
        touch($this->databasePath);

        config([
            'database.default' => 'sqlite',
            'database.connections.sqlite.database' => $this->databasePath,
        ]);

        DB::purge('sqlite');
        DB::reconnect('sqlite');
        Artisan::call('migrate:fresh', ['--force' => true]);
    }

    public function test_master_pode_restaurar_backup_valido_com_sqlite_e_arquivos_publicos()
    {
        Storage::fake('local');
        Storage::fake('r2');
        Storage::fake('public');

        $master = User::factory()->create([
            'role' => 'master',
            'email' => 'master@teste.com',
        ]);

        $club = Club::create([
            'nome' => 'Clube Original',
            'cidade' => 'Campinas',
            'associacao' => 'APaC',
        ]);

        Storage::disk('public')->put('logos/restored.txt', 'versao-do-backup');
        File::copy($this->databasePath, $this->backupDatabasePath);

        $zip = new ZipArchive;
        $zip->open($this->zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE);
        $zip->addFile($this->backupDatabasePath, 'database/database.sqlite');
        $zip->addFromString('app/public/logos/restored.txt', 'versao-do-backup');
        $zip->close();

        $club->update([
            'nome' => 'Clube Alterado',
            'cidade' => 'Sao Paulo',
        ]);
        Storage::disk('public')->put('logos/restored.txt', 'versao-atual');

        $pasta = config('backup.backup.name', 'Laravel');
        Storage::disk('local')->put($pasta.'/restore-flow-valid.zip', File::get($this->zipPath));

        $response = $this->actingAs($master)->post(route('backups.restore'), [
            'disk' => 'local',
            'path' => $pasta.'/restore-flow-valid.zip',
        ]);

        $response->assertRedirect('/login');
        $response->assertSessionHas('success');

        DB::purge('sqlite');
        DB::reconnect('sqlite');

        $this->assertDatabaseHas('clubs', [
            'nome' => 'Clube Original',
            'cidade' => 'Campinas',
        ]);

        $this->assertSame('versao-do-backup', Storage::disk('public')->get('logos/restored.txt'));
    }

    protected function tearDown(): void
    {
        DB::disconnect('sqlite');

        config([
            'database.default' => $this->originalDefaultConnection,
            'database.connections.sqlite.database' => $this->originalSqliteDatabase,
        ]);

        DB::purge('sqlite');

        @unlink($this->databasePath);
        @unlink($this->backupDatabasePath);
        @unlink($this->zipPath);

        parent::tearDown();
    }
}

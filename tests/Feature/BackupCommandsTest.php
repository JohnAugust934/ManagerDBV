<?php

namespace Tests\Feature;

use App\Models\BackupLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use ZipArchive;

class BackupCommandsTest extends TestCase
{
    use RefreshDatabase;

    private function pasta(): string
    {
        return config('backup.backup.name', 'Laravel');
    }

    private function putZip(string $name, array $entries): string
    {
        $tmp = tempnam(sys_get_temp_dir(), 'cmdtest_').'.zip';
        $zip = new ZipArchive;
        $zip->open($tmp, ZipArchive::CREATE | ZipArchive::OVERWRITE);
        foreach ($entries as $entry => $content) {
            $zip->addFromString($entry, $content);
        }
        $zip->close();

        $relative = $this->pasta().'/'.$name;
        Storage::disk('local')->put($relative, file_get_contents($tmp));
        @unlink($tmp);

        return $relative;
    }

    // ---- agendamento ------------------------------------------------------------

    public function test_novas_rotinas_de_backup_estao_agendadas(): void
    {
        $events = collect(app(\Illuminate\Console\Scheduling\Schedule::class)->events());

        $prune = $events->first(fn ($e) => str_contains($e->command, 'backup:prune-manifests'));
        $this->assertNotNull($prune, 'backup:prune-manifests não está agendado.');
        $this->assertSame('10 4 * * *', $prune->expression);
        $this->assertTrue($prune->onOneServer);

        $deep = $events->first(fn ($e) => str_contains($e->command, 'backup:deep-verify'));
        $this->assertNotNull($deep, 'backup:deep-verify não está agendado.');
        $this->assertSame('45 4 1 * *', $deep->expression, 'O deep-verify deveria rodar mensalmente no dia 1.');
        $this->assertTrue($deep->onOneServer);
    }

    // ---- backup:prune-manifests -------------------------------------------------

    public function test_prune_remove_manifest_orfao_mas_mantem_par_valido(): void
    {
        Storage::fake('local');
        Storage::fake('r2');
        config(['backup.backup.destination.disks' => ['local']]);

        $pasta = $this->pasta();

        // Par valido: zip + manifest.
        Storage::disk('local')->put($pasta.'/valido.zip', 'conteudo');
        Storage::disk('local')->put($pasta.'/valido.zip.manifest.json', '{"status":"success"}');

        // Orfao: manifest sem zip.
        Storage::disk('local')->put($pasta.'/orfao.zip.manifest.json', '{"status":"success"}');

        $this->artisan('backup:prune-manifests')->assertExitCode(0);

        Storage::disk('local')->assertExists($pasta.'/valido.zip');
        Storage::disk('local')->assertExists($pasta.'/valido.zip.manifest.json');
        Storage::disk('local')->assertMissing($pasta.'/orfao.zip.manifest.json');
    }

    public function test_prune_poda_backup_logs_antigos(): void
    {
        Storage::fake('local');
        Storage::fake('r2');
        config([
            'backup.backup.destination.disks' => ['local'],
            'app.env' => 'testing',
        ]);
        putenv('BACKUP_LOG_RETENTION_DAYS=30');
        $_ENV['BACKUP_LOG_RETENTION_DAYS'] = '30';

        $antigo = BackupLog::create(['disk' => 'local', 'status' => 'success']);
        $antigo->forceFill(['created_at' => now()->subDays(100)])->save();

        $recente = BackupLog::create(['disk' => 'local', 'status' => 'success']);

        $this->artisan('backup:prune-manifests')->assertExitCode(0);

        $this->assertDatabaseMissing('backup_logs', ['id' => $antigo->id]);
        $this->assertDatabaseHas('backup_logs', ['id' => $recente->id]);

        putenv('BACKUP_LOG_RETENTION_DAYS');
        unset($_ENV['BACKUP_LOG_RETENTION_DAYS']);
    }

    // ---- backup:deep-verify -----------------------------------------------------

    public function test_deep_verify_aprova_dump_sql_com_tabela_critica(): void
    {
        Http::fake();
        Storage::fake('local');
        Storage::fake('r2');
        config(['backup.backup.destination.disks' => ['local']]);

        $this->putZip('2026-06-15-03-00-00.zip', [
            'db-dumps/database.sql' => "CREATE TABLE desbravadores (id int);\nINSERT INTO desbravadores VALUES (1);\n",
            'app/public/logos/x.txt' => 'logo',
        ]);

        $this->artisan('backup:deep-verify --disk=local')->assertExitCode(0);
    }

    public function test_deep_verify_reprova_quando_nao_ha_dump(): void
    {
        Http::fake();
        Storage::fake('local');
        Storage::fake('r2');
        config(['backup.backup.destination.disks' => ['local']]);

        $this->putZip('2026-06-15-03-00-00.zip', [
            'app/public/logos/x.txt' => 'logo',
        ]);

        $this->artisan('backup:deep-verify --disk=local')->assertExitCode(1);
    }

    public function test_deep_verify_roda_integrity_check_em_sqlite(): void
    {
        Http::fake();
        Storage::fake('local');
        Storage::fake('r2');
        config(['backup.backup.destination.disks' => ['local']]);

        // Cria um SQLite real e valido para inspecao.
        $sqlitePath = tempnam(sys_get_temp_dir(), 'realsqlite_').'.sqlite';
        $pdo = new \PDO('sqlite:'.$sqlitePath);
        $pdo->exec('CREATE TABLE desbravadores (id INTEGER PRIMARY KEY)');
        $pdo->exec('INSERT INTO desbravadores (id) VALUES (1)');
        $pdo = null;

        $this->putZip('2026-06-15-03-00-00.zip', [
            'database/database.sqlite' => file_get_contents($sqlitePath),
        ]);
        @unlink($sqlitePath);

        $this->artisan('backup:deep-verify --disk=local')->assertExitCode(0);
    }
}

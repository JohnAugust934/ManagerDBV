<?php

namespace Tests\Feature;

use App\Models\BackupLog;
use App\Services\BackupIntegrityVerifier;
use App\Services\ScheduledTaskTracker;
use App\Services\TelegramNotifier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Spatie\Backup\Events\BackupWasSuccessful;
use Tests\TestCase;
use ZipArchive;

class BackupIntegrityVerifierTest extends TestCase
{
    use RefreshDatabase;

    private function makeZip(array $entries): string
    {
        $path = tempnam(sys_get_temp_dir(), 'bkptest_').'.zip';
        $zip = new ZipArchive;
        $zip->open($path, ZipArchive::CREATE | ZipArchive::OVERWRITE);
        foreach ($entries as $name => $content) {
            $zip->addFromString($name, $content);
        }
        $zip->close();

        return $path;
    }

    private function putBackup(string $contentZipPath, string $filename = '2026-06-15-03-00-00.zip'): string
    {
        $pasta = config('backup.backup.name', 'Laravel');
        $relative = $pasta.'/'.$filename;
        Storage::disk('local')->put($relative, file_get_contents($contentZipPath));
        @unlink($contentZipPath);

        return $pasta;
    }

    public function test_aprova_backup_valido_com_dump_e_uploads(): void
    {
        Storage::fake('local');
        config(['backup.integrity.min_size_kb' => 0]);

        $zip = $this->makeZip([
            'db-dumps/database.sql' => "-- dump\nINSERT INTO clubs VALUES (1);\n",
            'app/public/logos/clube.txt' => 'logo',
        ]);
        $pasta = $this->putBackup($zip);

        $report = app(BackupIntegrityVerifier::class)->verify('local', $pasta);

        $this->assertTrue($report['ok'], 'Backup valido deveria passar. Problemas: '.implode(' | ', $report['problems']));
        $this->assertTrue($report['has_database']);
        $this->assertTrue($report['has_uploads']);
        $this->assertSame(2, $report['files']);
        $this->assertNotNull($report['checksum']);
        $this->assertSame(64, strlen((string) $report['checksum']));
    }

    public function test_aceita_dump_sqlite_como_banco_presente(): void
    {
        Storage::fake('local');
        config(['backup.integrity.min_size_kb' => 0]);

        $zip = $this->makeZip([
            'database/database.sqlite' => 'SQLite format 3 fake bytes',
        ]);
        $pasta = $this->putBackup($zip);

        $report = app(BackupIntegrityVerifier::class)->verify('local', $pasta);

        $this->assertTrue($report['ok']);
        $this->assertTrue($report['has_database']);
        $this->assertFalse($report['has_uploads']);
    }

    public function test_reprova_backup_sem_dump_do_banco(): void
    {
        Storage::fake('local');
        config(['backup.integrity.min_size_kb' => 0]);

        $zip = $this->makeZip([
            'app/public/logos/clube.txt' => 'logo',
        ]);
        $pasta = $this->putBackup($zip);

        $report = app(BackupIntegrityVerifier::class)->verify('local', $pasta);

        $this->assertFalse($report['ok']);
        $this->assertFalse($report['has_database']);
        $this->assertNotEmpty($report['problems']);
        $this->assertStringContainsString('dump do banco', implode(' ', $report['problems']));
    }

    public function test_reprova_backup_abaixo_do_tamanho_minimo(): void
    {
        Storage::fake('local');
        config(['backup.integrity.min_size_kb' => 100000]); // exige ~100 MB

        $zip = $this->makeZip([
            'db-dumps/database.sql' => 'INSERT INTO clubs VALUES (1);',
        ]);
        $pasta = $this->putBackup($zip);

        $report = app(BackupIntegrityVerifier::class)->verify('local', $pasta);

        $this->assertFalse($report['ok']);
        $this->assertStringContainsString('Tamanho abaixo do minimo', implode(' ', $report['problems']));
    }

    public function test_reprova_quando_nao_ha_backup_no_disco(): void
    {
        Storage::fake('local');

        $pasta = config('backup.backup.name', 'Laravel');

        $report = app(BackupIntegrityVerifier::class)->verify('local', $pasta);

        $this->assertFalse($report['ok']);
        $this->assertStringContainsString('Nenhum arquivo de backup', implode(' ', $report['problems']));
    }

    public function test_integridade_falha_dispara_alerta_imediato_no_telegram(): void
    {
        Http::fake();
        Storage::fake('local');
        Cache::flush();

        config([
            'services.telegram.enabled' => true,
            'services.telegram.bot_token' => 'bot-token',
            'services.telegram.chat_id' => '123456',
            'services.telegram.admin_notifications' => true,
            'services.telegram.error_dedup_seconds' => 0,
            'cache.default' => 'array',
            'backup.integrity.enabled' => true,
            'backup.integrity.min_size_kb' => 0,
        ]);

        // Backup "verde" porem invalido: zip sem o dump do banco.
        $zip = $this->makeZip(['app/public/logos/clube.txt' => 'logo']);
        $pasta = $this->putBackup($zip);

        app(TelegramNotifier::class)->notifyBackupEvent(new BackupWasSuccessful('local', $pasta));

        Http::assertSent(fn ($request) => str_contains($request['text'], 'verificação de integridade'));

        $results = app(ScheduledTaskTracker::class)->getAll();
        $this->assertArrayHasKey('backup_run:local', $results);
        $this->assertSame('failure', $results['backup_run:local']['status']);

        // Falha tambem deve ser registrada no historico backup_logs.
        $this->assertDatabaseHas('backup_logs', [
            'disk' => 'local',
            'status' => 'failed',
            'has_database' => false,
        ]);
    }

    public function test_integridade_ok_registra_sucesso_enriquecido_sem_telegram(): void
    {
        Http::fake();
        Storage::fake('local');
        Cache::flush();

        config([
            'services.telegram.enabled' => true,
            'services.telegram.bot_token' => 'bot-token',
            'services.telegram.chat_id' => '123456',
            'services.telegram.admin_notifications' => true,
            'services.telegram.error_dedup_seconds' => 0,
            'cache.default' => 'array',
            'backup.integrity.enabled' => true,
            'backup.integrity.min_size_kb' => 0,
        ]);

        $zip = $this->makeZip([
            'db-dumps/database.sql' => "-- dump\nINSERT INTO clubs VALUES (1);\n",
            'app/public/logos/clube.txt' => 'logo',
        ]);
        $pasta = $this->putBackup($zip);

        app(TelegramNotifier::class)->notifyBackupEvent(new BackupWasSuccessful('local', $pasta));

        // Sucesso integro nao dispara alerta imediato.
        Http::assertNothingSent();

        $results = app(ScheduledTaskTracker::class)->getAll();
        $this->assertArrayHasKey('backup_run:local', $results);
        $this->assertSame('success', $results['backup_run:local']['status']);
        $this->assertArrayHasKey('Checksum', $results['backup_run:local']['details']);
        $this->assertArrayHasKey('Tamanho', $results['backup_run:local']['details']);

        // Manifest.json sidecar gravado ao lado do zip.
        $manifestFiles = collect(Storage::disk('local')->allFiles($pasta))
            ->filter(fn ($f) => str_ends_with($f, '.manifest.json'));
        $this->assertCount(1, $manifestFiles);

        $manifest = json_decode(Storage::disk('local')->get($manifestFiles->first()), true);
        $this->assertSame('success', $manifest['status']);
        $this->assertNotEmpty($manifest['archive']['sha256']);
        $this->assertCount(2, $manifest['files']);
        foreach ($manifest['files'] as $file) {
            $this->assertSame(64, strlen((string) $file['sha256']), 'Cada arquivo deve ter SHA-256.');
        }

        // Historico em backup_logs com status sucesso e manifest persistido.
        $log = BackupLog::where('disk', 'local')->first();
        $this->assertNotNull($log);
        $this->assertSame('success', $log->status);
        $this->assertTrue($log->has_database);
        $this->assertTrue($log->has_uploads);
        $this->assertIsArray($log->manifest);
    }
}

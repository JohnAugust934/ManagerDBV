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
        $arquivos = Storage::disk('local')->files($pasta);
        $this->assertCount(1, $arquivos);
        $this->assertStringStartsWith($pasta.'/meu_backup_antigo-', $arquivos[0]);
        $this->assertStringEndsWith('.zip', $arquivos[0]);
    }

    public function test_master_nao_pode_importar_zip_corrompido()
    {
        Storage::fake('local');
        Storage::fake('r2');
        $master = User::factory()->create(['role' => 'master']);

        $file = UploadedFile::fake()->createWithContent('backup-corrompido.zip', 'nao-e-um-zip-real');

        $response = $this->actingAs($master)->post(route('backups.import'), [
            'backup_file' => $file,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error', 'O arquivo enviado não é um ZIP válido ou está corrompido.');

        $pasta = config('backup.backup.name', 'Laravel');
        $this->assertSame([], Storage::disk('local')->files($pasta));
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
        Artisan::shouldNotHaveReceived('call', ['db:wipe', ['--force' => true]]);
        Artisan::shouldNotHaveReceived('call', ['migrate', ['--force' => true]]);
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

    public function test_listagem_exibe_backups_guardados_sob_pasta_de_nome_antigo()
    {
        Storage::fake('local');
        Storage::fake('r2');
        $master = User::factory()->create(['role' => 'master']);

        // Backup criado quando o APP_NAME (e, portanto, a pasta de destino) era
        // diferente do atual. A listagem recursiva deve continuar exibindo-o.
        Storage::disk('local')->put('NomeAntigoDoClube/2026-06-12-08-22-23.zip', 'conteudo-fake');

        $response = $this->actingAs($master)->get(route('backups.index'));

        $response->assertOk();
        $response->assertSee('2026-06-12-08-22-23.zip');
    }

    public function test_backup_de_arquivos_inclui_apenas_uploads()
    {
        // O zip deve conter apenas os uploads dos usuarios (storage/app/public),
        // unica parte de arquivos que a restauracao consome. Zipar base_path()
        // inteiro arrastava arquivos volateis (sessao, cache, logs, .git, o proprio
        // zip temporario) que mudavam durante a execucao e quebravam o fechamento
        // do arquivo com "ZipArchive::close(): Invalid argument". O banco continua
        // sendo salvo separadamente (source.databases).
        $this->assertSame([storage_path('app/public')], config('backup.backup.source.files.include'));
        $this->assertTrue(config('backup.backup.source.files.ignore_unreadable_directories'));
    }

    public function test_normalize_backup_selection_bloqueia_traversal_mas_aceita_legados()
    {
        $controller = new \App\Http\Controllers\BackupController;
        $method = new \ReflectionMethod($controller, 'normalizeBackupSelection');
        $method->setAccessible(true);

        // Aceita backup sob qualquer pasta (inclusive nomes antigos), sem exigir
        // mais um prefixo fixo de pasta.
        $this->assertSame(
            ['local', 'NomeAntigoDoClube/2026-06-12-08-22-23.zip'],
            $method->invoke($controller, 'local', 'NomeAntigoDoClube/2026-06-12-08-22-23.zip')
        );

        // Ainda bloqueia path traversal e caminhos absolutos.
        $maliciosos = [
            '../../etc/passwd.zip',
            'Laravel/../../secret.zip',
            'pasta/./oculto.zip',
            'C:/Windows/system32/config.zip',
        ];

        foreach ($maliciosos as $caminho) {
            try {
                $method->invoke($controller, 'local', $caminho);
                $this->fail("Path traversal não foi bloqueado para: {$caminho}");
            } catch (\RuntimeException $e) {
                $this->assertStringContainsString('fora do diretório', $e->getMessage());
            }
        }

        // Disco fora da allowlist tambem e rejeitado.
        $this->expectException(\RuntimeException::class);
        $method->invoke($controller, 'disco_invalido', 'Laravel/x.zip');
    }

    public function test_rotinas_de_backup_estao_agendadas()
    {
        $schedule = app()->make(\Illuminate\Console\Scheduling\Schedule::class);
        $events = collect($schedule->events());

        $backupClean = $events->first(fn ($event) => str_contains($event->command, 'backup:clean'));
        $this->assertNotNull($backupClean, 'O agendamento de limpeza de backups nao foi encontrado.');
        $this->assertEquals('0 4 * * *', $backupClean->expression, 'A limpeza nao esta agendada para as 04:00 da manha.');
        $this->assertTrue($backupClean->withoutOverlapping, 'O backup:clean deveria evitar sobreposicao.');
        $this->assertTrue($backupClean->onOneServer, 'O backup:clean deveria rodar em apenas um servidor.');

        $backupRun = $events->first(fn ($event) => str_contains($event->command, 'backup:run'));
        $this->assertNotNull($backupRun, 'O agendamento de criacao de backup nao foi encontrado.');
        $this->assertEquals('0 3 * * *', $backupRun->expression, 'O backup nao esta agendado para as 03:00 da manha.');
        $this->assertTrue($backupRun->withoutOverlapping, 'O backup:run deveria evitar sobreposicao.');
        $this->assertTrue($backupRun->onOneServer, 'O backup:run deveria rodar em apenas um servidor.');

        $backupMonitor = $events->first(fn ($event) => str_contains($event->command, 'backup:monitor'));
        $this->assertNotNull($backupMonitor, 'O agendamento de monitoramento de backups nao foi encontrado.');
        $this->assertEquals('30 4 * * *', $backupMonitor->expression, 'O monitoramento nao esta agendado para as 04:30 da manha.');
        $this->assertTrue($backupMonitor->withoutOverlapping, 'O backup:monitor deveria evitar sobreposicao.');
        $this->assertTrue($backupMonitor->onOneServer, 'O backup:monitor deveria rodar em apenas um servidor.');

        $queueMonitor = $events->first(fn ($event) => str_contains($event->command, 'queue:monitor'));
        $this->assertNotNull($queueMonitor, 'O monitoramento de fila nao foi encontrado.');
        $this->assertTrue($queueMonitor->withoutOverlapping, 'O queue:monitor deveria evitar sobreposicao.');
        $this->assertTrue($queueMonitor->onOneServer, 'O queue:monitor deveria rodar em apenas um servidor.');

        $eventReflection = new \ReflectionClass($queueMonitor);
        $rejectsProperty = $eventReflection->getProperty('rejects');
        $rejectsProperty->setAccessible(true);
        $this->assertGreaterThan(0, count($rejectsProperty->getValue($queueMonitor)), 'O queue:monitor deveria respeitar janela de pausa do backup.');

        $rankingSnapshot = $events->first(fn ($event) => str_contains($event->command, 'ranking:snapshot'));
        $this->assertNotNull($rankingSnapshot, 'O snapshot anual do ranking nao foi encontrado.');
    }

    public function test_configuracao_de_backup_usa_defaults_seguros_e_monitora_local_e_r2()
    {
        $this->assertTrue(config('backup.backup.verify_backup'));
        $this->assertSame(['local', 'r2'], config('backup.backup.destination.disks'));
        $this->assertSame(['local', 'r2'], config('backup.monitor_backups.0.disks'));
        // Resiliencia: por padrao a falha de um destino (ex.: R2) nao deve
        // abortar o backup local. Ver test_disco_r2_sem_bucket_e_descartado.
        $this->assertTrue(config('backup.backup.destination.continue_on_failure'));
        $this->assertSame('default', config('backup.backup.encryption'));
        $this->assertIsArray(config('backup.notifications.mail.to'));
        $this->assertNotEmpty(config('backup.notifications.mail.to'));
        $this->assertSame([], config('backup.notifications.notifications.'.\Spatie\Backup\Notifications\Notifications\BackupWasSuccessfulNotification::class));
    }

    public function test_configuracao_de_backup_pode_ser_personalizada_por_variaveis_de_ambiente()
    {
        $this->setBackupEnv('BACKUP_DESTINATION_DISKS', 'local');
        $this->setBackupEnv('BACKUP_MONITOR_DISKS', 'r2');
        $this->setBackupEnv('BACKUP_NOTIFICATIONS_MAIL_TO', 'ops@clube.com,admin@clube.com');
        $this->setBackupEnv('BACKUP_MAIL_NOTIFICATIONS', 'true');
        $this->setBackupEnv('BACKUP_VERIFY', 'false');
        $this->setBackupEnv('BACKUP_ARCHIVE_ENCRYPTION', 'aes256');
        $this->setBackupEnv('BACKUP_MONITOR_MAX_AGE_DAYS', '3');
        $this->setBackupEnv('BACKUP_MONITOR_MAX_STORAGE_MB', '2048');

        $config = require base_path('config/backup.php');

        $this->assertSame(['local'], $config['backup']['destination']['disks']);
        $this->assertSame(['r2'], $config['monitor_backups'][0]['disks']);
        $this->assertSame(['ops@clube.com', 'admin@clube.com'], $config['notifications']['mail']['to']);
        $this->assertSame(['mail'], $config['notifications']['notifications'][\Spatie\Backup\Notifications\Notifications\BackupWasSuccessfulNotification::class]);
        $this->assertFalse($config['backup']['verify_backup']);
        $this->assertSame('aes256', $config['backup']['encryption']);
        $this->assertSame(3, $config['monitor_backups'][0]['health_checks'][\Spatie\Backup\Tasks\Monitor\HealthChecks\MaximumAgeInDays::class]);
        $this->assertSame(2048, $config['monitor_backups'][0]['health_checks'][\Spatie\Backup\Tasks\Monitor\HealthChecks\MaximumStorageInMegabytes::class]);

        $this->setBackupEnv('BACKUP_DESTINATION_DISKS', null);
        $this->setBackupEnv('BACKUP_MONITOR_DISKS', null);
        $this->setBackupEnv('BACKUP_NOTIFICATIONS_MAIL_TO', null);
        $this->setBackupEnv('BACKUP_MAIL_NOTIFICATIONS', null);
        $this->setBackupEnv('BACKUP_VERIFY', null);
        $this->setBackupEnv('BACKUP_ARCHIVE_ENCRYPTION', null);
        $this->setBackupEnv('BACKUP_MONITOR_MAX_AGE_DAYS', null);
        $this->setBackupEnv('BACKUP_MONITOR_MAX_STORAGE_MB', null);
    }

    public function test_disco_r2_sem_bucket_e_descartado_para_nao_quebrar_o_backup()
    {
        // Reproduz o cenario de producao que derrubava o backup: r2 listado nos
        // destinos mas sem R2_BUCKET configurado. O disco de nuvem deve ser
        // descartado em vez de gerar bucket nulo (TypeError no AwsS3V3Adapter),
        // degradando para o disco local.
        $this->setBackupEnv('BACKUP_DESTINATION_DISKS', 'local,r2');
        $this->setBackupEnv('BACKUP_MONITOR_DISKS', 'local,r2');
        $this->setBackupEnv('R2_BUCKET', null);

        $config = require base_path('config/backup.php');

        $this->assertSame(['local'], $config['backup']['destination']['disks']);
        $this->assertSame(['local'], $config['monitor_backups'][0]['disks']);

        // Com o bucket presente o r2 volta a ser mantido nos destinos.
        $this->setBackupEnv('R2_BUCKET', 'managerdbv-test-bucket');

        $configComR2 = require base_path('config/backup.php');

        $this->assertSame(['local', 'r2'], $configComR2['backup']['destination']['disks']);

        $this->setBackupEnv('BACKUP_DESTINATION_DISKS', null);
        $this->setBackupEnv('BACKUP_MONITOR_DISKS', null);
    }

    private function setBackupEnv(string $key, ?string $value): void
    {
        if ($value === null) {
            putenv($key);
            unset($_ENV[$key], $_SERVER[$key]);

            return;
        }

        putenv("{$key}={$value}");
        $_ENV[$key] = $value;
        $_SERVER[$key] = $value;
    }
}


<?php

namespace Tests\Feature;

use App\Services\ScheduledTaskTracker;
use App\Services\TelegramNotifier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Spatie\Backup\Events\BackupHasFailed;
use Spatie\Backup\Events\BackupWasSuccessful;
use Spatie\Backup\Events\CleanupHasFailed;
use Spatie\Backup\Events\CleanupWasSuccessful;
use Spatie\Backup\Events\HealthyBackupWasFound;
use Tests\TestCase;

class TelegramNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_notificador_envia_alerta_administrativo_para_telegram()
    {
        Http::fake();

        config([
            'services.telegram.enabled' => true,
            'services.telegram.bot_token' => 'bot-token',
            'services.telegram.chat_id' => '123456',
            'services.telegram.admin_notifications' => true,
            'services.telegram.error_notifications' => true,
            'services.telegram.error_dedup_seconds' => 0,
            'cache.default' => 'array',
        ]);

        app(TelegramNotifier::class)->notifyAdministrativeAction('Backup manual executado', [
            'Responsavel' => 'Master',
            'Origem' => 'Tela de backups',
        ], 'success');

        Http::assertSent(function ($request) {
            return $request->url() === 'https://api.telegram.org/botbot-token/sendMessage'
                && $request['chat_id'] === '123456'
                && str_contains($request['text'], 'Backup manual executado')
                && str_contains($request['text'], 'Tela de backups');
        });
    }

    public function test_evento_de_backup_sucesso_nao_envia_telegram_mas_registra_no_cache(): void
    {
        Http::fake();

        config([
            'services.telegram.enabled'             => true,
            'services.telegram.bot_token'           => 'bot-token',
            'services.telegram.chat_id'             => '123456',
            'services.telegram.admin_notifications' => true,
            'services.telegram.error_dedup_seconds' => 0,
            'cache.default'                         => 'array',
        ]);

        Cache::flush();

        app(TelegramNotifier::class)->notifyBackupEvent(new BackupWasSuccessful('local', 'DBV Manager'));

        // Sucesso NÃO deve disparar Telegram
        Http::assertNothingSent();

        // Sucesso DEVE registrar no Cache via ScheduledTaskTracker
        $results = app(ScheduledTaskTracker::class)->getAll();
        $this->assertArrayHasKey('backup_run', $results);
        $this->assertSame('success', $results['backup_run']['status']);
        $this->assertSame('Geração de Backup', $results['backup_run']['label']);
    }

    public function test_evento_de_backup_falha_envia_telegram_imediatamente(): void
    {
        Http::fake();

        config([
            'services.telegram.enabled'             => true,
            'services.telegram.bot_token'           => 'bot-token',
            'services.telegram.chat_id'             => '123456',
            'services.telegram.admin_notifications' => true,
            'services.telegram.error_dedup_seconds' => 0,
            'cache.default'                         => 'array',
        ]);

        Cache::flush();

        $exception = new \RuntimeException('Disco cheio');
        app(TelegramNotifier::class)->notifyBackupEvent(new BackupHasFailed($exception, 'local', 'DBV Manager'));

        // Falha DEVE disparar Telegram imediatamente
        Http::assertSent(function ($request) {
            return str_contains($request['text'], 'Falha ao gerar backup')
                && str_contains($request['text'], 'Disco cheio');
        });

        // Falha também DEVE registrar no Cache
        $results = app(ScheduledTaskTracker::class)->getAll();
        $this->assertArrayHasKey('backup_run', $results);
        $this->assertSame('failure', $results['backup_run']['status']);
    }

    public function test_evento_limpeza_sucesso_registra_no_cache_sem_telegram(): void
    {
        Http::fake();

        config([
            'services.telegram.enabled'             => true,
            'services.telegram.bot_token'           => 'bot-token',
            'services.telegram.chat_id'             => '123456',
            'services.telegram.admin_notifications' => true,
            'services.telegram.error_dedup_seconds' => 0,
            'cache.default'                         => 'array',
        ]);

        Cache::flush();

        app(TelegramNotifier::class)->notifyBackupEvent(new CleanupWasSuccessful('local', 'DBV Manager'));

        Http::assertNothingSent();

        $results = app(ScheduledTaskTracker::class)->getAll();
        $this->assertArrayHasKey('backup_clean', $results);
        $this->assertSame('success', $results['backup_clean']['status']);
    }

    public function test_evento_limpeza_falha_envia_telegram_imediatamente(): void
    {
        Http::fake();

        config([
            'services.telegram.enabled'             => true,
            'services.telegram.bot_token'           => 'bot-token',
            'services.telegram.chat_id'             => '123456',
            'services.telegram.admin_notifications' => true,
            'services.telegram.error_dedup_seconds' => 0,
            'cache.default'                         => 'array',
        ]);

        Cache::flush();

        $exception = new \RuntimeException('Falha de limpeza');
        app(TelegramNotifier::class)->notifyBackupEvent(new CleanupHasFailed($exception, 'local', 'DBV Manager'));

        Http::assertSent(function ($request) {
            return str_contains($request['text'], 'Falha na limpeza de backups')
                && str_contains($request['text'], 'Falha de limpeza');
        });
    }

    public function test_evento_monitoramento_saudavel_registra_no_cache_sem_telegram(): void
    {
        Http::fake();

        config([
            'services.telegram.enabled'             => true,
            'services.telegram.bot_token'           => 'bot-token',
            'services.telegram.chat_id'             => '123456',
            'services.telegram.admin_notifications' => true,
            'services.telegram.error_dedup_seconds' => 0,
            'cache.default'                         => 'array',
        ]);

        Cache::flush();

        app(TelegramNotifier::class)->notifyBackupEvent(new HealthyBackupWasFound('local', 'DBV Manager'));

        Http::assertNothingSent();

        $results = app(ScheduledTaskTracker::class)->getAll();
        $this->assertArrayHasKey('backup_monitor', $results);
        $this->assertSame('success', $results['backup_monitor']['status']);
    }

    public function test_notify_scheduled_failure_envia_alerta_de_erro(): void
    {
        Http::fake();

        config([
            'services.telegram.enabled'             => true,
            'services.telegram.bot_token'           => 'bot-token',
            'services.telegram.chat_id'             => '123456',
            'services.telegram.admin_notifications' => true,
            'services.telegram.error_dedup_seconds' => 0,
            'cache.default'                         => 'array',
        ]);

        app(TelegramNotifier::class)->notifyScheduledFailure('Falha crítica na sincronização', [
            'Erro' => 'Timeout na API',
        ]);

        Http::assertSent(function ($request) {
            return str_contains($request['text'], 'Falha crítica na sincronização')
                && str_contains($request['text'], 'Timeout na API');
        });
    }

    public function test_send_daily_summary_envia_mensagem_consolidada(): void
    {
        Http::fake();

        config([
            'services.telegram.enabled'             => true,
            'services.telegram.bot_token'           => 'bot-token',
            'services.telegram.chat_id'             => '123456',
            'services.telegram.admin_notifications' => true,
            'services.telegram.error_dedup_seconds' => 0,
            'cache.default'                         => 'array',
        ]);

        $results = [
            'backup_run' => [
                'status'      => 'success',
                'label'       => 'Geração de Backup',
                'details'     => ['Backup' => 'dbv', 'Disco' => 's3'],
                'recorded_at' => now()->toIso8601String(),
            ],
            'backup_clean' => [
                'status'      => 'failure',
                'label'       => 'Limpeza de Backups',
                'reason'      => 'Espaço insuficiente',
                'recorded_at' => now()->toIso8601String(),
            ],
        ];

        app(TelegramNotifier::class)->sendDailySummary($results);

        Http::assertSent(function ($request) {
            $text = $request['text'];

            return str_contains($text, 'Relatório Diário')
                && str_contains($text, 'Geração de Backup')
                && str_contains($text, 'Sucesso')
                && str_contains($text, 'Limpeza de Backups')
                && str_contains($text, 'Falha')
                && str_contains($text, 'Espaço insuficiente');
        });
    }

    public function test_notificador_pode_enviar_erro_critico_para_telegram()
    {
        Http::fake();

        config([
            'services.telegram.enabled' => true,
            'services.telegram.bot_token' => 'bot-token',
            'services.telegram.chat_id' => '123456',
            'services.telegram.error_notifications' => true,
            'services.telegram.error_dedup_seconds' => 0,
            'cache.default' => 'array',
        ]);

        app(TelegramNotifier::class)->notifyException(new \RuntimeException('Falha critica no sistema'));

        Http::assertSent(function ($request) {
            return str_contains($request['text'], 'Erro no sistema')
                && str_contains($request['text'], 'Falha critica no sistema');
        });
    }

    public function test_notificador_nao_envia_quando_telegram_esta_desabilitado()
    {
        Http::fake();

        config([
            'services.telegram.enabled' => false,
            'services.telegram.bot_token' => null,
            'services.telegram.chat_id' => null,
            'services.telegram.error_dedup_seconds' => 0,
            'cache.default' => 'array',
        ]);

        app(TelegramNotifier::class)->notifyAdministrativeAction('Teste');

        Http::assertNothingSent();
    }

    public function test_notificador_deduplica_erros_iguais_durante_janela_de_protecao()
    {
        Http::fake();

        config([
            'services.telegram.enabled' => true,
            'services.telegram.bot_token' => 'bot-token',
            'services.telegram.chat_id' => '123456',
            'services.telegram.error_notifications' => true,
            'services.telegram.error_dedup_seconds' => 300,
            'services.telegram.suppress_transient_db_errors' => false,
            'cache.default' => 'array',
        ]);

        $exception = new \RuntimeException('Falha critica no sistema');

        app(TelegramNotifier::class)->notifyException($exception);
        app(TelegramNotifier::class)->notifyException($exception);

        Http::assertSentCount(1);
    }

    public function test_notificador_pode_desabilitar_deduplicacao_de_erros()
    {
        Http::fake();

        config([
            'services.telegram.enabled' => true,
            'services.telegram.bot_token' => 'bot-token',
            'services.telegram.chat_id' => '123456',
            'services.telegram.error_notifications' => true,
            'services.telegram.error_dedup_seconds' => 0,
            'services.telegram.suppress_transient_db_errors' => false,
            'cache.default' => 'array',
        ]);

        $exception = new \RuntimeException('Falha critica no sistema');

        app(TelegramNotifier::class)->notifyException($exception);
        app(TelegramNotifier::class)->notifyException($exception);

        Http::assertSentCount(2);
    }

    public function test_notificador_suprime_erro_transitorio_de_banco_na_janela_configurada()
    {
        Http::fake();

        config([
            'services.telegram.enabled' => true,
            'services.telegram.bot_token' => 'bot-token',
            'services.telegram.chat_id' => '123456',
            'services.telegram.error_notifications' => true,
            'services.telegram.error_dedup_seconds' => 0,
            'services.telegram.suppress_transient_db_errors' => true,
            'services.telegram.transient_db_suppress_windows' => '00:00-23:59',
            'cache.default' => 'array',
        ]);

        app(TelegramNotifier::class)->notifyException(
            new \PDOException('SQLSTATE[HY000] [2002] Connection refused')
        );

        Http::assertNothingSent();
    }
}

<?php

namespace Tests\Feature;

use App\Console\Commands\DailyBackupReport;
use App\Services\ScheduledTaskTracker;
use App\Services\TelegramNotifier;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class DailyBackupReportCommandTest extends TestCase
{
    private function telegramConfig(): void
    {
        config([
            'services.telegram.enabled'             => true,
            'services.telegram.bot_token'           => 'test-token',
            'services.telegram.chat_id'             => '123456',
            'services.telegram.admin_notifications' => true,
            'services.telegram.error_notifications' => true,
            'services.telegram.error_dedup_seconds' => 0,
            'cache.default'                         => 'array',
        ]);
    }

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
    }

    public function test_envia_resumo_consolidado_com_todos_os_sucessos(): void
    {
        Http::fake();
        $this->telegramConfig();

        $tracker = app(ScheduledTaskTracker::class);
        $tracker->recordSuccess('backup_run', 'Geração de Backup', ['Backup' => 'dbv', 'Disco' => 's3']);
        $tracker->recordSuccess('backup_clean', 'Limpeza de Backups', ['Backup' => 'dbv', 'Disco' => 's3']);
        $tracker->recordSuccess('backup_monitor', 'Monitoramento de Backup', ['Backup' => 'dbv', 'Disco' => 's3']);

        $this->artisan('daily:backup-report')->assertSuccessful();

        Http::assertSent(function ($request) {
            $text = $request['text'];

            return str_contains($text, 'Relatório Diário')
                && str_contains($text, 'Geração de Backup')
                && str_contains($text, 'Limpeza de Backups')
                && str_contains($text, 'Monitoramento de Backup')
                && str_contains($text, 'Sucesso');
        });
    }

    public function test_envia_resumo_com_falha_destacada(): void
    {
        Http::fake();
        $this->telegramConfig();

        $tracker = app(ScheduledTaskTracker::class);
        $tracker->recordSuccess('backup_run', 'Geração de Backup', ['Backup' => 'dbv', 'Disco' => 's3']);
        $tracker->recordFailure('backup_clean', 'Limpeza de Backups', 'Erro: disco cheio');
        $tracker->recordSuccess('backup_monitor', 'Monitoramento de Backup', ['Backup' => 'dbv', 'Disco' => 's3']);

        $this->artisan('daily:backup-report')->assertSuccessful();

        Http::assertSent(function ($request) {
            $text = $request['text'];

            return str_contains($text, 'Limpeza de Backups')
                && str_contains($text, 'Falha')
                && str_contains($text, 'disco cheio');
        });
    }

    public function test_envia_aviso_quando_nenhuma_tarefa_foi_registrada(): void
    {
        Http::fake();
        $this->telegramConfig();

        // Cache vazio — nenhuma tarefa rodou
        $this->artisan('daily:backup-report')->assertSuccessful();

        Http::assertSent(function ($request) {
            return str_contains($request['text'], 'Nenhuma tarefa registrou resultado');
        });
    }

    public function test_limpa_o_cache_apos_envio(): void
    {
        Http::fake();
        $this->telegramConfig();

        $tracker = app(ScheduledTaskTracker::class);
        $tracker->recordSuccess('backup_run', 'Geração de Backup');

        $this->assertFalse($tracker->isEmpty());

        $this->artisan('daily:backup-report')->assertSuccessful();

        $this->assertTrue($tracker->isEmpty());
    }

    public function test_nao_envia_telegram_quando_notificacoes_desabilitadas(): void
    {
        Http::fake();

        config([
            'services.telegram.enabled'             => false,
            'services.telegram.admin_notifications' => false,
            'cache.default'                         => 'array',
        ]);

        $tracker = app(ScheduledTaskTracker::class);
        $tracker->recordSuccess('backup_run', 'Geração de Backup');

        $this->artisan('daily:backup-report')->assertSuccessful();

        Http::assertNothingSent();
    }

    public function test_resumo_contem_data_e_ambiente(): void
    {
        Http::fake();
        $this->telegramConfig();

        $tracker = app(ScheduledTaskTracker::class);
        $tracker->recordSuccess('backup_run', 'Geração de Backup');

        $this->artisan('daily:backup-report')->assertSuccessful();

        Http::assertSent(function ($request) {
            $text = $request['text'];

            return str_contains($text, 'Data:')
                && str_contains($text, 'Ambiente:')
                && str_contains($text, 'Gerado às');
        });
    }
}

<?php

namespace Tests\Feature;

use App\Services\TelegramNotifier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Spatie\Backup\Events\BackupWasSuccessful;
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

    public function test_evento_de_backup_pode_ser_formatado_para_telegram()
    {
        Http::fake();

        config([
            'services.telegram.enabled' => true,
            'services.telegram.bot_token' => 'bot-token',
            'services.telegram.chat_id' => '123456',
            'services.telegram.admin_notifications' => true,
            'services.telegram.error_dedup_seconds' => 0,
            'cache.default' => 'array',
        ]);

        app(TelegramNotifier::class)->notifyBackupEvent(new BackupWasSuccessful('local', 'DBV Manager'));

        Http::assertSent(function ($request) {
            return str_contains($request['text'], 'Backup concluido com sucesso')
                && str_contains($request['text'], 'DBV Manager')
                && str_contains($request['text'], 'local');
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

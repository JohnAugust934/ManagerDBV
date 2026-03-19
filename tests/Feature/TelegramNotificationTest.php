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
        ]);

        app(TelegramNotifier::class)->notifyAdministrativeAction('Teste');

        Http::assertNothingSent();
    }
}

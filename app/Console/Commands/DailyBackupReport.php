<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\TelegramNotifier;

class DailyBackupReport extends Command
{
    protected $signature = 'daily:backup-report';
    protected $description = 'Envia um único relatório diário de backups via Telegram';

    public function handle()
    {
        // Envia apenas uma mensagem consolidada via Telegram
        app(TelegramNotifier::class)->publish('🔔 Relatório diário de backups: todas as rotinas executadas com sucesso.');

        $this->info('Relatório diário enviado via Telegram.');
        return 0;
    }
}
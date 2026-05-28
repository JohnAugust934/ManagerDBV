<?php

namespace App\Console\Commands;

use App\Services\ScheduledTaskTracker;
use App\Services\TelegramNotifier;
use Illuminate\Console\Command;

class DailyBackupReport extends Command
{
    protected $signature = 'daily:backup-report';

    protected $description = 'Envia um único relatório diário consolidado de backups via Telegram';

    public function handle(TelegramNotifier $telegram, ScheduledTaskTracker $tracker): int
    {
        $results = $tracker->getAll();

        if (empty($results)) {
            $this->warn('Nenhum resultado de tarefa encontrado no Cache. Enviando aviso ao Telegram.');
        } else {
            $total    = count($results);
            $failures = collect($results)->where('status', 'failure')->count();
            $this->info("Lendo {$total} resultado(s) do Cache ({$failures} falha(s)).");
        }

        // Envia a mensagem consolidada (mesmo se vazia — avisa que nada foi registrado)
        $telegram->sendDailySummary($results);

        // Limpa os dados do Cache após o envio bem-sucedido
        $tracker->flush();

        $this->info('Relatório diário enviado e Cache limpo.');

        return self::SUCCESS;
    }
}
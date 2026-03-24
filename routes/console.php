<?php

use App\Providers\AppServiceProvider;
use App\Support\OperationalWindow;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// ==========================================
// AGENDAMENTO AUTOMÁTICO DE BACKUPS (CRON)
// ==========================================
// Agenda para rodar todos os dias às 03:00 (Horário de Brasília)
Schedule::command('backup:run')
    ->timezone('America/Sao_Paulo')
    ->dailyAt('03:00')
    ->withoutOverlapping(120)
    ->onOneServer();

// Também agenda a limpeza dos backups antigos
Schedule::command('backup:clean')
    ->timezone('America/Sao_Paulo')
    ->dailyAt('04:00')
    ->withoutOverlapping(90)
    ->onOneServer();

// Monitora a saúde dos backups após a janela de criação/limpeza
Schedule::command('backup:monitor')
    ->timezone('America/Sao_Paulo')
    ->dailyAt('04:30')
    ->withoutOverlapping(60)
    ->onOneServer();

if ((bool) env('QUEUE_MONITOR_ENABLED', true)) {
    $queueConnection = env('QUEUE_CONNECTION', 'database');
    $queueName = env('QUEUE_MONITOR_QUEUE', 'default');
    $maxSize = (int) env('QUEUE_MONITOR_MAX_SIZE', 50);
    $pauseWindows = (string) env('QUEUE_MONITOR_PAUSE_WINDOWS', '02:45-04:45');

    $queueMonitorEvent = Schedule::command("queue:monitor {$queueConnection}:{$queueName} --max={$maxSize}")
        ->timezone('America/Sao_Paulo')
        ->withoutOverlapping(10)
        ->onOneServer()
        ->runInBackground()
        ->everyFiveMinutes();

    foreach (OperationalWindow::parseWindows($pauseWindows) as $window) {
        $queueMonitorEvent->unlessBetween($window['start'], $window['end']);
    }
}

Artisan::command('ranking:snapshot {year?}', function (?int $year = null) {
    $snapshotYear = $year ?: now()->subYear()->year;

    AppServiceProvider::snapshotRankingYear($snapshotYear);

    $this->info("Snapshot anual do ranking gerado para {$snapshotYear}.");
})->purpose('Gera um snapshot anual do ranking para auditoria');

Schedule::command('ranking:snapshot')
    ->timezone('America/Sao_Paulo')
    ->yearlyOn(1, 1, '00:10');

<?php

use App\Providers\AppServiceProvider;
use App\Support\OperationalWindow;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Schedule;

// ==== BACKUP AUTOMATED SCHEDULES ====
Schedule::command('backup:run')
    ->timezone('America/Sao_Paulo')
    ->dailyAt('03:00')
    ->withoutOverlapping(120)
    ->onOneServer();

Schedule::command('backup:clean')
    ->timezone('America/Sao_Paulo')
    ->dailyAt('04:00')
    ->withoutOverlapping(90)
    ->onOneServer();

Schedule::command('backup:monitor')
    ->timezone('America/Sao_Paulo')
    ->dailyAt('04:30')
    ->withoutOverlapping(60)
    ->onOneServer();

// ==== DAILY REPORT (TELEGRAM) ====
Schedule::command('daily:backup-report')
    ->timezone('America/Sao_Paulo')
    ->dailyAt('05:00')
    ->withoutOverlapping()
    ->onOneServer();

if ((bool) env('QUEUE_MONITOR_ENABLED', false)) {
    $queueConnection = env('QUEUE_CONNECTION', 'database');
    $queueName = env('QUEUE_MONITOR_QUEUE', 'default');
    $maxSize = (int) env('QUEUE_MONITOR_MAX_SIZE', 50);
    $pauseWindows = (string) env('QUEUE_MONITOR_PAUSE_WINDOWS', '02:45-04:45');

    $scheduleEvent = Schedule::command("queue:monitor {$queueConnection}:{$queueName} --max={$maxSize}")
        ->timezone('America/Sao_Paulo')
        ->withoutOverlapping(10)
        ->onOneServer()
        ->runInBackground()
        ->everyFiveMinutes();

    foreach (OperationalWindow::parseWindows($pauseWindows) as $window) {
        $scheduleEvent->unlessBetween($window['start'], $window['end']);
    }
}

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('ranking:snapshot {year?}', function (?int $year = null) {
    $snapshotYear = $year ?: now()->subYear()->year;

    AppServiceProvider::snapshotRankingYear($snapshotYear);

    $this->info("Snapshot anual do ranking gerado para {$snapshotYear}.");
})->purpose('Gera um snapshot anual do ranking para auditoria');

Schedule::command('ranking:snapshot')
    ->timezone('America/Sao_Paulo')
    ->yearlyOn(1, 1, '00:10');
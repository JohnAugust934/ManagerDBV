<?php

namespace App\Console;

use App\Support\OperationalWindow;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected function schedule(Schedule $schedule)
    {
        // ==== BACKUP AUTOMATED SCHEDULES ====
        // Runs every day at 03:00 (Brasília timezone)
        $schedule->command('backup:run')
            ->timezone('America/Sao_Paulo')
            ->dailyAt('03:00')
            ->withoutOverlapping(120)
            ->onOneServer();

        // Cleanup runs every day at 04:00 (Brasília timezone)
        $schedule->command('backup:clean')
            ->timezone('America/Sao_Paulo')
            ->dailyAt('04:00')
            ->withoutOverlapping(90)
            ->onOneServer();

        // Health check runs every day at 04:30 (Brasília timezone)
        $schedule->command('backup:monitor')
            ->timezone('America/Sao_Paulo')
            ->dailyAt('04:30')
            ->withoutOverlapping(60)
            ->onOneServer();

        // ==== DAILY REPORT (TELEGRAM) ====
        // Generates a single consolidated Telegram message at 05:00 (Brasília timezone)
        $schedule->command('daily:backup-report')
            ->timezone('America/Sao_Paulo')
            ->dailyAt('05:00')
            ->withoutOverlapping()
            ->onOneServer();

        // ==== OPTIONAL QUEUE MONITOR ====
        if ((bool) env('QUEUE_MONITOR_ENABLED', false)) {
            $queueConnection = env('QUEUE_CONNECTION', 'database');
            $queueName = env('QUEUE_MONITOR_QUEUE', 'default');
            $maxSize = (int) env('QUEUE_MONITOR_MAX_SIZE', 50);
            $pauseWindows = (string) env('QUEUE_MONITOR_PAUSE_WINDOWS', '02:45-04:45');

            $schedule->command("queue:monitor {$queueConnection}:{$queueName} --max={$maxSize}")
                ->timezone('America/Sao_Paulo')
                ->withoutOverlapping(10)
                ->onOneServer()
                ->runInBackground()
                ->everyFiveMinutes();

            foreach (OperationalWindow::parseWindows($pauseWindows) as $window) {
                $schedule->command(...)->unlessBetween($window['start'], $window['end']);
            }
        }
    }

    /**
     * Register the console commands for the application.
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');
    }
}
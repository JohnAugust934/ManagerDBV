<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// ==========================================
// AGENDAMENTO AUTOMÁTICO DE BACKUPS (CRON)
// ==========================================

// 1. Limpeza (01:00 AM) - Verifica a retenção e apaga arquivos velhos
Schedule::command('backup:clean', ['--disable-notifications' => true])->dailyAt('01:00');

// 2. Criação (03:00 AM) - Varre o banco de dados e arquivos e joga na nuvem
Schedule::command('backup:run', ['--disable-notifications' => true])->dailyAt('03:00');

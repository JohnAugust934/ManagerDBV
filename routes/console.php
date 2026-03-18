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
// Agenda para rodar todos os dias às 03:00 (Horário de Brasília)
Schedule::command('backup:run')
    ->timezone('America/Sao_Paulo')
    ->dailyAt('03:00');

// Também agenda a limpeza dos backups antigos
Schedule::command('backup:clean')
    ->timezone('America/Sao_Paulo')
    ->dailyAt('04:00');

// Monitora a saúde dos backups após a janela de criação/limpeza
Schedule::command('backup:monitor')
    ->timezone('America/Sao_Paulo')
    ->dailyAt('04:30');

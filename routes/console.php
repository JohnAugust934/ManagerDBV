<?php

use App\Providers\AppServiceProvider;
use App\Support\OperationalWindow;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
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

// Remove PDFs de relatórios expirados (gerados em batch via fila, expiram em 24h).
Schedule::call(function () {
    $expirados = \App\Models\RelatorioGerado::withoutGlobalScopes()
        ->where('expires_at', '<', now())
        ->whereNotNull('arquivo')
        ->get();

    foreach ($expirados as $relatorio) {
        \Illuminate\Support\Facades\Storage::disk('local')->delete($relatorio->arquivo);
        $relatorio->update(['arquivo' => null, 'status' => 'expirado']);
    }
})
    ->timezone('America/Sao_Paulo')
    ->hourly()
    ->name('relatorios:limpar-expirados')
    ->withoutOverlapping(30);

// Remove manifests orfaos (.manifest.json sem zip) deixados apos o backup:clean
// e poda o historico antigo de backup_logs. Roda logo apos a limpeza do spatie.
Schedule::command('backup:prune-manifests')
    ->timezone('America/Sao_Paulo')
    ->dailyAt('04:10')
    ->withoutOverlapping(60)
    ->onOneServer();

// Verificacao PROFUNDA mensal: abre e le o dump do banco dentro do backup mais
// recente para detectar corrupcao silenciosa (alem das checagens diarias).
Schedule::command('backup:deep-verify')
    ->timezone('America/Sao_Paulo')
    ->monthlyOn(1, '04:45')
    ->withoutOverlapping(120)
    ->onOneServer();

// ==== DAILY REPORT (TELEGRAM) ====
Schedule::command('daily:backup-report')
    ->timezone('America/Sao_Paulo')
    ->dailyAt('05:00')
    ->withoutOverlapping()
    ->onOneServer();

// ==== LIMPEZA OPERACIONAL ====

// Remove PDFs de relatórios batch expirados (gerados via fila, expiram em 24h).
// Nota: o cleanup horário em memória já existe (closure acima); esta versão
// agendada garante cobertura mesmo se o schedule horário falhar.
Schedule::call(function () {
    $expirados = \App\Models\RelatorioGerado::withoutGlobalScopes()
        ->where('expires_at', '<', now())
        ->where('status', '!=', 'expirado')
        ->whereNotNull('arquivo')
        ->get();

    foreach ($expirados as $relatorio) {
        \Illuminate\Support\Facades\Storage::disk('local')->delete($relatorio->arquivo);
        $relatorio->update(['arquivo' => null, 'status' => 'expirado']);
    }
})
    ->timezone('America/Sao_Paulo')
    ->dailyAt('03:30')
    ->name('relatorios:limpar-expirados-diario')
    ->withoutOverlapping(30);

// Remove jobs falhos com mais de 7 dias da tabela failed_jobs.
Schedule::command('queue:prune-failed', ['--hours' => 168])
    ->timezone('America/Sao_Paulo')
    ->weekly()
    ->onOneServer();

// Limpa sessões expiradas do banco (relevante quando SESSION_DRIVER=database).
Schedule::command('session:gc')
    ->timezone('America/Sao_Paulo')
    ->dailyAt('03:45')
    ->onOneServer();

// LGPD: anonimiza desbravadores desligados há mais de 5 anos (Art. 14).
Schedule::command('lgpd:anonimizar-desligados')
    ->timezone('America/Sao_Paulo')
    ->monthly()
    ->onOneServer();

// ==== FILA ====

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

    // Multi-tenant: gera um snapshot por clube. Sem usuario autenticado, os global
    // scopes ficam inertes, por isso iteramos os clubes explicitamente.
    $clubIds = \App\Models\Club::query()->pluck('id');

    foreach ($clubIds as $clubId) {
        AppServiceProvider::snapshotRankingYear($snapshotYear, (int) $clubId);
    }

    $this->info("Snapshot anual do ranking gerado para {$snapshotYear} ({$clubIds->count()} clube(s)).");
})->purpose('Gera um snapshot anual do ranking para auditoria');

Schedule::command('ranking:snapshot')
    ->timezone('America/Sao_Paulo')
    ->yearlyOn(1, 1, '00:10');

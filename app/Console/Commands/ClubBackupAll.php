<?php

namespace App\Console\Commands;

use App\Models\Club;
use App\Services\ClubBackupService;
use App\Services\TelegramNotifier;
use Illuminate\Console\Command;

/**
 * Gera um backup de cada clube ativo.
 * Agendado diariamente; roda em sequência para não sobrecarregar I/O.
 *
 * Uso:
 *   php artisan club:backup-all
 *   php artisan club:backup-all --club=3   (apenas um clube específico)
 */
class ClubBackupAll extends Command
{
    protected $signature = 'club:backup-all {--club= : ID de clube específico (opcional)}';

    protected $description = 'Gera backup isolado de todos os clubes ativos (ou de um clube específico)';

    public function handle(ClubBackupService $backupService, TelegramNotifier $telegram): int
    {
        $clubId = $this->option('club');

        $query = Club::query()->where('is_active', true);
        if ($clubId) {
            $query->where('id', (int) $clubId);
        }

        $clubs = $query->get();

        if ($clubs->isEmpty()) {
            $this->warn('Nenhum clube ativo encontrado.');

            return self::SUCCESS;
        }

        $success = 0;
        $failed = 0;
        $failedNames = [];

        foreach ($clubs as $club) {
            $this->line("→ Backup do clube: {$club->nome} (#{$club->id})");

            try {
                $result = $backupService->backup($club, origin: 'scheduled');
                $sizeMb = round($result['size'] / 1048576, 2);
                $this->info("  ✓ {$result['filename']} ({$sizeMb} MB) → ".implode(', ', $result['disks']));
                $success++;
            } catch (\Throwable $e) {
                $this->error("  ✗ Falha: {$e->getMessage()}");
                $failed++;
                $failedNames[] = $club->nome;
            }
        }

        $this->newLine();
        $this->info("Concluído: {$success} ok, {$failed} falha(s).");

        if ($failed > 0) {
            try {
                $telegram->send(
                    "⚠️ *club:backup-all* — {$failed} clube(s) falharam:\n".
                    implode("\n", array_map(fn ($n) => "  • {$n}", $failedNames))
                );
            } catch (\Throwable) {
            }

            return self::FAILURE;
        }

        return self::SUCCESS;
    }
}

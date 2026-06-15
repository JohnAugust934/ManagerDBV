<?php

namespace App\Console\Commands;

use App\Models\BackupLog;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Throwable;

/**
 * Remove manifests "orfaos" (.manifest.json sem o .zip correspondente) e poda o
 * historico antigo de backup_logs. Necessario porque o backup:clean do spatie so
 * remove arquivos .zip — os sidecars de manifest ficariam acumulando para sempre.
 * Agendado logo apos o backup:clean.
 */
class PruneBackupManifests extends Command
{
    protected $signature = 'backup:prune-manifests';

    protected $description = 'Remove manifests órfãos (sem zip) e poda o histórico antigo de backup_logs';

    public function handle(): int
    {
        $disks = config('backup.backup.destination.disks', ['local']);
        $removedManifests = 0;

        foreach ($disks as $disk) {
            try {
                $removedManifests += $this->pruneOrphanManifests($disk);
            } catch (Throwable $exception) {
                $this->warn("Falha ao limpar manifests no disco {$disk}: {$exception->getMessage()}");
                report($exception);
            }
        }

        $prunedLogs = $this->pruneOldLogs();

        $this->info("Manifests órfãos removidos: {$removedManifests}. Linhas de backup_logs podadas: {$prunedLogs}.");

        return self::SUCCESS;
    }

    /**
     * Remove cada .manifest.json cujo .zip irmao nao existe mais no disco.
     */
    private function pruneOrphanManifests(string $disk): int
    {
        $files = Storage::disk($disk)->allFiles();
        $removed = 0;

        foreach ($files as $file) {
            if (! str_ends_with($file, '.manifest.json')) {
                continue;
            }

            $zipPath = substr($file, 0, -strlen('.manifest.json'));

            if (! Storage::disk($disk)->exists($zipPath)) {
                Storage::disk($disk)->delete($file);
                $removed++;
            }
        }

        return $removed;
    }

    /**
     * Poda linhas de backup_logs mais antigas que BACKUP_LOG_RETENTION_DAYS
     * (default 365). Mantemos historico bem mais longo que os zips, pois e barato
     * (sem o conteudo do backup) e util para auditoria.
     */
    private function pruneOldLogs(): int
    {
        $days = max(1, (int) env('BACKUP_LOG_RETENTION_DAYS', 365));
        $cutoff = Carbon::now()->subDays($days);

        return BackupLog::where('created_at', '<', $cutoff)->delete();
    }
}

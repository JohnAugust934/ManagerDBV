<?php

namespace App\Console\Commands;

use App\Services\TelegramNotifier;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Spatie\Backup\BackupDestination\BackupDestination;
use Throwable;
use ZipArchive;

/**
 * Verificacao PROFUNDA (mensal) do backup mais recente: alem das checagens
 * rapidas do BackupIntegrityVerifier (tamanho/zip/checksum), este comando ABRE e
 * LE o dump do banco dentro do zip para confirmar que nao esta corrompido —
 * sem tocar no banco em producao.
 *
 *  - Dump .sql (MySQL/PostgreSQL): confirma que e texto legivel e contem
 *    estrutura/dados de tabelas criticas conhecidas (compatibilidade dupla).
 *  - Dump .sqlite: abre via PDO e roda "PRAGMA integrity_check".
 *
 * Resultado vai para o Telegram (sucesso resumido ou falha detalhada).
 */
class DeepVerifyBackup extends Command
{
    protected $signature = 'backup:deep-verify {--disk= : Disco a inspecionar (default: primeiro destino configurado)}';

    protected $description = 'Verificação profunda mensal: abre e lê o dump do banco dentro do backup mais recente';

    /** Tabelas criticas que devem aparecer no dump SQL (qualquer uma basta). */
    private const CRITICAL_TABLES = ['desbravadores', 'caixas', 'users', 'mensalidades'];

    public function handle(TelegramNotifier $telegram): int
    {
        $disk = (string) ($this->option('disk') ?: (config('backup.backup.destination.disks', ['local'])[0] ?? 'local'));
        $backupName = (string) config('backup.backup.name', 'laravel-backup');

        $tempZip = null;
        $tempDump = null;

        try {
            $backup = BackupDestination::create($disk, $backupName)->newestBackup();

            if ($backup === null || ! $backup->exists()) {
                return $this->failWith($telegram, $disk, 'Nenhum backup encontrado para verificação profunda.');
            }

            // Caminho local do zip (baixa do R2 se necessario).
            if (config("filesystems.disks.{$disk}.driver") === 'local') {
                $zipPath = Storage::disk($disk)->path($backup->path());
            } else {
                $tempZip = tempnam(sys_get_temp_dir(), 'deepvrf_');
                file_put_contents($tempZip, Storage::disk($disk)->get($backup->path()));
                $zipPath = $tempZip;
            }

            [$dumpEntry, $dumpType] = $this->locateDumpEntry($zipPath);

            if ($dumpEntry === null) {
                return $this->failWith($telegram, $disk, 'O backup não contém um dump do banco para inspeção.');
            }

            $tempDump = $this->extractEntry($zipPath, $dumpEntry);

            $problem = $dumpType === 'sqlite'
                ? $this->inspectSqliteDump($tempDump)
                : $this->inspectSqlDump($tempDump);

            if ($problem !== null) {
                return $this->failWith($telegram, $disk, $problem, basename($backup->path()));
            }

            $this->info('Verificação profunda concluída com sucesso.');
            $telegram->notifyAdministrativeAction('Verificação profunda do backup OK', [
                'Disco' => $disk,
                'Arquivo' => basename($backup->path()),
                'Dump' => $dumpEntry,
                'Tipo' => strtoupper($dumpType),
            ], 'success');

            return self::SUCCESS;
        } catch (Throwable $exception) {
            report($exception);

            return $this->failWith($telegram, $disk, 'Erro inesperado na verificação profunda: '.$exception->getMessage());
        } finally {
            if ($tempZip !== null && is_file($tempZip)) {
                @unlink($tempZip);
            }
            if ($tempDump !== null && is_file($tempDump)) {
                @unlink($tempDump);
            }
        }
    }

    /**
     * Localiza a entrada do dump no zip. Retorna [entryName, 'sql'|'sqlite'] ou [null, null].
     *
     * @return array{0: ?string, 1: ?string}
     */
    private function locateDumpEntry(string $zipPath): array
    {
        $zip = new ZipArchive;
        if ($zip->open($zipPath) !== true) {
            return [null, null];
        }

        try {
            for ($i = 0; $i < $zip->numFiles; $i++) {
                $entry = str_replace('\\', '/', (string) $zip->getNameIndex($i));
                $lower = strtolower($entry);

                if (str_contains($lower, 'db-dumps') && str_ends_with($lower, '.sql')) {
                    return [$entry, 'sql'];
                }

                if (str_contains('/'.$lower, '/database/') && str_ends_with($lower, '.sqlite')) {
                    return [$entry, 'sqlite'];
                }
            }
        } finally {
            $zip->close();
        }

        return [null, null];
    }

    private function extractEntry(string $zipPath, string $entry): string
    {
        $zip = new ZipArchive;
        if ($zip->open($zipPath) !== true) {
            throw new \RuntimeException('Não foi possível reabrir o zip para extrair o dump.');
        }

        try {
            $stream = $zip->getStream($entry);
            if (! is_resource($stream)) {
                throw new \RuntimeException("Não foi possível ler a entrada '{$entry}' do backup.");
            }

            $temp = tempnam(sys_get_temp_dir(), 'deepdump_');
            $target = fopen($temp, 'wb');
            try {
                stream_copy_to_stream($stream, $target);
            } finally {
                fclose($stream);
                fclose($target);
            }

            return $temp;
        } finally {
            $zip->close();
        }
    }

    /**
     * Valida um dump SQL textual (MySQL ou PostgreSQL). Retorna a mensagem de
     * problema, ou null se estiver ok.
     */
    private function inspectSqlDump(string $dumpPath): ?string
    {
        $size = filesize($dumpPath) ?: 0;
        if ($size < 1) {
            return 'O dump SQL está vazio.';
        }

        // Le um trecho generoso (cabecalho + parte do corpo) para procurar
        // estrutura/dados. Dumps de ambos os bancos contem "CREATE TABLE" e os
        // nomes das tabelas.
        $contents = (string) file_get_contents($dumpPath);
        $haystack = strtolower($contents);

        if (! str_contains($haystack, 'create table') && ! str_contains($haystack, 'insert into')) {
            return 'O dump SQL não contém estrutura (CREATE TABLE) nem dados (INSERT INTO) reconhecíveis.';
        }

        foreach (self::CRITICAL_TABLES as $table) {
            if (str_contains($haystack, $table)) {
                return null; // achou ao menos uma tabela critica → dump plausivel
            }
        }

        return 'O dump SQL não referencia nenhuma das tabelas críticas esperadas ('.implode(', ', self::CRITICAL_TABLES).').';
    }

    /**
     * Abre o dump SQLite via PDO e roda PRAGMA integrity_check.
     */
    private function inspectSqliteDump(string $dumpPath): ?string
    {
        try {
            $pdo = new \PDO('sqlite:'.$dumpPath);
            $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

            $result = $pdo->query('PRAGMA integrity_check')->fetchColumn();

            if (strtolower((string) $result) !== 'ok') {
                return 'PRAGMA integrity_check retornou: '.$result;
            }

            return null;
        } catch (Throwable $exception) {
            return 'Falha ao abrir o SQLite do backup: '.$exception->getMessage();
        }
    }

    private function failWith(TelegramNotifier $telegram, string $disk, string $reason, ?string $arquivo = null): int
    {
        $this->error($reason);

        $telegram->notifyScheduledFailure('Verificação profunda do backup FALHOU', array_filter([
            'Disco' => $disk,
            'Arquivo' => $arquivo,
            'Motivo' => $reason,
        ]));

        return self::FAILURE;
    }
}

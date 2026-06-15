<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Spatie\Backup\BackupDestination\BackupDestination;
use Throwable;
use ZipArchive;

/**
 * Verificacao de integridade PROPRIA do backup, complementar ao verify nativo
 * do spatie. Roda apos cada backup bem-sucedido (por disco), localiza o zip mais
 * recente e confere:
 *   1. tamanho minimo (pega zip vazio/truncado);
 *   2. reabertura do zip (ZipArchive) e que contem ao menos um arquivo;
 *   3. presenca do dump do banco e (informativo) dos uploads;
 *   4. checksum SHA-256 do arquivo (para conferencia futura entre discos).
 *
 * Objetivo: um zip "verde" porem invalido nao passa mais despercebido — o
 * TelegramNotifier transforma o resultado em alerta imediato quando falha.
 */
class BackupIntegrityVerifier
{
    /**
     * Verifica o backup mais recente do par disco/nome informado.
     *
     * @return array{
     *     ok: bool,
     *     disk: string,
     *     backup_name: string,
     *     filename: ?string,
     *     path: ?string,
     *     size_bytes: int,
     *     size_human: string,
     *     files: int,
     *     checksum: ?string,
     *     has_database: bool,
     *     has_uploads: bool,
     *     entries: array<int, array{name: string, size: int, sha256: ?string}>,
     *     problems: array<int, string>
     * }
     */
    public function verify(string $diskName, string $backupName): array
    {
        $result = [
            'ok' => false,
            'disk' => $diskName,
            'backup_name' => $backupName,
            'filename' => null,
            'path' => null,
            'size_bytes' => 0,
            'size_human' => '0 B',
            'files' => 0,
            'checksum' => null,
            'has_database' => false,
            'has_uploads' => false,
            'entries' => [],
            'problems' => [],
        ];

        $localCopy = null;

        try {
            $backup = BackupDestination::create($diskName, $backupName)->newestBackup();

            if ($backup === null || ! $backup->exists()) {
                $result['problems'][] = 'Nenhum arquivo de backup encontrado no disco.';

                return $result;
            }

            $result['filename'] = basename($backup->path());
            $result['path'] = $backup->path();
            $sizeBytes = (int) $backup->sizeInBytes();
            $result['size_bytes'] = $sizeBytes;
            $result['size_human'] = $this->humanSize($sizeBytes);

            $minSizeBytes = max(0, (int) config('backup.integrity.min_size_kb', 50)) * 1024;
            if ($sizeBytes < $minSizeBytes) {
                $result['problems'][] = sprintf(
                    'Tamanho abaixo do minimo: %s (minimo %s).',
                    $result['size_human'],
                    $this->humanSize($minSizeBytes)
                );
            }

            // Garante um caminho local para abrir o zip e calcular o checksum.
            // Disco local: usa o caminho direto. Nuvem (R2): baixa para temp.
            $localPath = $this->resolveLocalPath($diskName, $backup->path(), $localCopy);

            $result['checksum'] = hash_file('sha256', $localPath) ?: null;

            $this->inspectArchive($localPath, $result);

            $result['ok'] = $result['problems'] === [];
        } catch (Throwable $exception) {
            $result['problems'][] = 'Falha ao verificar o backup: '.$exception->getMessage();
        } finally {
            if ($localCopy !== null && is_file($localCopy)) {
                @unlink($localCopy);
            }
        }

        return $result;
    }

    /**
     * Monta o manifest (metadados + checksums por arquivo) a partir do relatorio
     * de verify(). Equivale ao manifest.json descrito no plano, porem adaptado
     * ao formato de zip unico do spatie.
     *
     * @param  array<string, mixed>  $report
     * @return array<string, mixed>
     */
    public function buildManifest(array $report): array
    {
        return [
            'generated_at' => now()->toIso8601String(),
            'app' => config('app.name'),
            'environment' => app()->environment(),
            'disk' => $report['disk'] ?? null,
            'backup_name' => $report['backup_name'] ?? null,
            'filename' => $report['filename'] ?? null,
            'status' => ($report['ok'] ?? false) ? 'success' : 'failed',
            'archive' => [
                'size_bytes' => $report['size_bytes'] ?? 0,
                'size_human' => $report['size_human'] ?? '0 B',
                'sha256' => $report['checksum'] ?? null,
            ],
            'has_database' => $report['has_database'] ?? false,
            'has_uploads' => $report['has_uploads'] ?? false,
            'files' => $report['entries'] ?? [],
            'problems' => $report['problems'] ?? [],
        ];
    }

    /**
     * Persiste o resultado da verificacao: grava o manifest.json ao lado do zip
     * (mesmo disco) e registra uma linha em backup_logs (historico). Resiliente:
     * nenhuma falha aqui pode derrubar o fluxo de backup/notificacao.
     *
     * @param  array<string, mixed>  $report
     * @return array<string, mixed> manifest gerado
     */
    public function persistResult(array $report): array
    {
        $manifest = $this->buildManifest($report);

        // 1) Sidecar manifest.json ao lado do zip (auditoria humana). Orfaos sao
        //    removidos por backup:prune-manifests apos o backup:clean do spatie.
        try {
            $disk = (string) ($report['disk'] ?? '');
            $path = (string) ($report['path'] ?? '');
            if ($disk !== '' && $path !== '') {
                $manifestPath = $this->manifestPathFor($path);
                Storage::disk($disk)->put($manifestPath, json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
            }
        } catch (Throwable $exception) {
            report($exception);
        }

        // 2) Historico em backup_logs.
        try {
            \App\Models\BackupLog::create([
                'disk' => $report['disk'] ?? 'desconhecido',
                'backup_name' => $report['backup_name'] ?? null,
                'filename' => $report['filename'] ?? null,
                'path' => $report['path'] ?? null,
                'status' => ($report['ok'] ?? false) ? 'success' : 'failed',
                'size_bytes' => $report['size_bytes'] ?? 0,
                'files_count' => $report['files'] ?? 0,
                'checksum' => $report['checksum'] ?? null,
                'has_database' => $report['has_database'] ?? false,
                'has_uploads' => $report['has_uploads'] ?? false,
                'problems' => empty($report['problems']) ? null : implode(' | ', $report['problems']),
                'manifest' => $manifest,
            ]);
        } catch (Throwable $exception) {
            report($exception);
        }

        return $manifest;
    }

    /**
     * Caminho do manifest sidecar para um dado zip (ex.: pasta/2026-...zip ->
     * pasta/2026-...zip.manifest.json).
     */
    public function manifestPathFor(string $zipPath): string
    {
        return $zipPath.'.manifest.json';
    }

    /**
     * Garante um caminho de arquivo local para o zip. Para discos de nuvem,
     * baixa para um arquivo temporario (devolvido em $localCopy para limpeza).
     */
    private function resolveLocalPath(string $diskName, string $path, ?string &$localCopy): string
    {
        if (config("filesystems.disks.{$diskName}.driver") === 'local') {
            return Storage::disk($diskName)->path($path);
        }

        $localCopy = tempnam(sys_get_temp_dir(), 'bkpchk_');
        if ($localCopy === false) {
            throw new \RuntimeException('Nao foi possivel criar arquivo temporario para verificacao.');
        }

        $source = Storage::disk($diskName)->readStream($path);
        if (! is_resource($source)) {
            throw new \RuntimeException('Nao foi possivel ler o backup do disco para verificacao.');
        }

        $target = fopen($localCopy, 'wb');
        if ($target === false) {
            fclose($source);
            throw new \RuntimeException('Nao foi possivel preparar o arquivo temporario de verificacao.');
        }

        try {
            stream_copy_to_stream($source, $target);
        } finally {
            fclose($source);
            fclose($target);
        }

        return $localCopy;
    }

    /**
     * Abre o zip, conta os arquivos e detecta o dump do banco e os uploads.
     *
     * @param  array<string, mixed>  $result  (passado por referencia)
     */
    private function inspectArchive(string $localPath, array &$result): void
    {
        $zip = new ZipArchive;
        $opened = $zip->open($localPath);

        if ($opened !== true) {
            $result['problems'][] = 'O arquivo nao e um ZIP valido ou esta corrompido.';

            return;
        }

        try {
            $result['files'] = $zip->numFiles;

            if ($zip->numFiles === 0) {
                $result['problems'][] = 'O ZIP do backup esta vazio.';

                return;
            }

            $entries = [];

            for ($index = 0; $index < $zip->numFiles; $index++) {
                $rawName = (string) $zip->getNameIndex($index);
                $entry = str_replace('\\', '/', $rawName);
                $lower = strtolower($entry);

                // Diretorios nao entram no manifest de checksums.
                if (str_ends_with($entry, '/')) {
                    continue;
                }

                $stat = $zip->statIndex($index) ?: [];

                $entries[] = [
                    'name' => $entry,
                    'size' => (int) ($stat['size'] ?? 0),
                    'sha256' => $this->hashEntry($zip, $rawName),
                ];

                // Mesma convencao usada por BackupController::restore. O nome de
                // entrada do zip nao tem barra inicial, por isso prefixamos '/'
                // para casar com o padrao '/database/' do restore.
                if (str_contains($lower, 'db-dumps') && str_ends_with($lower, '.sql')) {
                    $result['has_database'] = true;
                }

                if (str_contains('/'.$lower, '/database/') && str_ends_with($lower, '.sqlite')) {
                    $result['has_database'] = true;
                }

                if (str_contains($entry, 'app/public/')) {
                    $result['has_uploads'] = true;
                }
            }

            $result['entries'] = $entries;

            if (! $result['has_database']) {
                $result['problems'][] = 'O backup nao contem o dump do banco de dados.';
            }
        } finally {
            $zip->close();
        }
    }

    /**
     * Calcula o SHA-256 de uma entrada do zip via stream (sem extrair p/ disco).
     */
    private function hashEntry(ZipArchive $zip, string $entryName): ?string
    {
        $stream = $zip->getStream($entryName);
        if (! is_resource($stream)) {
            return null;
        }

        $ctx = hash_init('sha256');
        try {
            while (! feof($stream)) {
                $buffer = fread($stream, 1048576);
                if ($buffer === false) {
                    break;
                }
                hash_update($ctx, $buffer);
            }
        } finally {
            fclose($stream);
        }

        return hash_final($ctx);
    }

    private function humanSize(int $bytes): string
    {
        if ($bytes <= 0) {
            return '0 B';
        }

        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $power = min((int) floor(log($bytes, 1024)), count($units) - 1);
        $value = $bytes / (1024 ** $power);

        return ($power === 0 ? (string) $bytes : number_format($value, 2)).' '.$units[$power];
    }
}

<?php

namespace App\Services;

use App\Models\Club;
use App\Models\ClubBackupLog;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use ZipArchive;

/**
 * Gera e armazena backups isolados por clube.
 *
 * O ZIP produzido contém:
 *   manifest.json  — metadados (clube, versão, checksums de uploads)
 *   data.json      — dump JSON de todos os dados do clube (via ClubExportService)
 *   uploads/       — arquivos de logo e fotos daquele clube
 *
 * Os arquivos ficam em backups/clubes/{slug}/ no disco local e (se configurado) no R2.
 * Nenhum dado de outro clube vaza para o ZIP.
 */
class ClubBackupService
{
    public const MANIFEST_VERSION = 1;

    public function __construct(
        private readonly ClubExportService $exporter
    ) {}

    /**
     * Gera o backup do clube e salva nos discos configurados.
     * Retorna o relatório (path, size, status).
     */
    public function backup(Club $club, string $origin = 'manual', ?int $createdBy = null): array
    {
        $slug = Str::slug($club->nome);
        $timestamp = now()->format('Y-m-d-H-i-s');
        $filename = "club-{$slug}-{$timestamp}.zip";
        $storagePath = "backups/clubes/{$slug}/{$filename}";

        $tempZipPath = storage_path("app/temp-club-backup-{$club->id}-".time().'.zip');

        try {
            $data = $this->exporter->export($club);
            $uploadFiles = $this->collectUploadFiles($data);

            $this->buildZip($tempZipPath, $data, $uploadFiles, $club);

            $size = filesize($tempZipPath);
            $checksum = hash_file('sha256', $tempZipPath);
            $hasUploads = count($uploadFiles) > 0;

            $savedDisks = $this->saveToDisks($tempZipPath, $storagePath);

            @unlink($tempZipPath);

            // Registra um log por disco
            $result = [
                'filename' => $filename,
                'path' => $storagePath,
                'size' => $size,
                'checksum' => $checksum,
                'has_uploads' => $hasUploads,
                'disks' => $savedDisks,
                'status' => 'success',
            ];

            foreach ($savedDisks as $disk) {
                ClubBackupLog::create([
                    'club_id' => $club->id,
                    'disk' => $disk,
                    'path' => $storagePath,
                    'filename' => $filename,
                    'status' => 'success',
                    'size_bytes' => $size,
                    'checksum' => $checksum,
                    'has_uploads' => $hasUploads,
                    'created_by' => $createdBy,
                    'origin' => $origin,
                ]);
            }

            return $result;

        } catch (\Throwable $e) {
            @unlink($tempZipPath);
            Log::error("ClubBackupService: falha no backup do clube {$club->id}", ['error' => $e->getMessage()]);

            ClubBackupLog::create([
                'club_id' => $club->id,
                'disk' => 'local',
                'path' => $storagePath,
                'filename' => $filename,
                'status' => 'failed',
                'size_bytes' => 0,
                'error' => $e->getMessage(),
                'created_by' => $createdBy,
                'origin' => $origin,
            ]);

            throw $e;
        }
    }

    /**
     * Lista os backups de um clube em todos os discos (local + R2), mais recentes primeiro.
     */
    public function listBackups(Club $club): array
    {
        $slug = Str::slug($club->nome);
        $prefix = "backups/clubes/{$slug}";
        $backups = [];

        foreach ($this->activeDisks() as $disk) {
            try {
                foreach (Storage::disk($disk)->listContents($prefix, false) as $item) {
                    if (! $item->isFile() || ! str_ends_with(strtolower($item->path()), '.zip')) {
                        continue;
                    }

                    $backups[] = [
                        'disk' => $disk,
                        'path' => $item->path(),
                        'name' => basename($item->path()),
                        'size' => round(($item->fileSize() ?? 0) / 1048576, 2),
                        'date' => \Carbon\Carbon::createFromTimestamp($item->lastModified() ?? 0),
                    ];
                }
            } catch (\Throwable $e) {
                Log::warning("ClubBackupService: erro ao listar backups no disco {$disk}: {$e->getMessage()}");
            }
        }

        usort($backups, fn ($a, $b) => $b['date'] <=> $a['date']);

        return $backups;
    }

    /** Verifica que path e disco pertencem ao clube (evita acesso cruzado). */
    public function assertBelongsToClub(Club $club, string $disk, string $path): void
    {
        $slug = Str::slug($club->nome);
        $expectedPrefix = "backups/clubes/{$slug}/";

        if (! in_array($disk, $this->activeDisks(), true)) {
            throw new \RuntimeException('Disco inválido.');
        }

        $normalized = str_replace('\\', '/', ltrim($path, '/\\'));

        if (str_contains($normalized, '../') || ! str_starts_with($normalized, $expectedPrefix)) {
            throw new \RuntimeException('Arquivo não pertence aos backups deste clube.');
        }

        if (! str_ends_with(strtolower($normalized), '.zip')) {
            throw new \RuntimeException('Arquivo inválido.');
        }
    }

    /** Constrói o arquivo ZIP do backup. */
    private function buildZip(string $zipPath, array $data, array $uploadFiles, Club $club): void
    {
        File::ensureDirectoryExists(dirname($zipPath));

        $zip = new ZipArchive;
        $result = $zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE);

        if ($result !== true) {
            throw new \RuntimeException("Não foi possível criar o arquivo ZIP temporário (código {$result}).");
        }

        try {
            // data.json
            $jsonContent = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            if ($jsonContent === false) {
                throw new \RuntimeException('Falha ao serializar os dados do clube em JSON.');
            }
            $zip->addFromString('data.json', $jsonContent);

            // uploads
            $uploadChecksums = [];
            foreach ($uploadFiles as $relativePath => $absolutePath) {
                if (File::exists($absolutePath)) {
                    $zip->addFile($absolutePath, 'uploads/'.$relativePath);
                    $uploadChecksums[$relativePath] = hash_file('sha256', $absolutePath);
                }
            }

            // manifest.json
            $manifest = [
                'version' => self::MANIFEST_VERSION,
                'generated_at' => now()->toIso8601String(),
                'club_id' => $club->id,
                'club_nome' => $club->nome,
                'app_version' => config('app.version', '1.0'),
                'upload_checksums' => $uploadChecksums,
            ];
            $zip->addFromString('manifest.json', json_encode($manifest, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));

        } finally {
            if (! $zip->close()) {
                throw new \RuntimeException('Falha ao fechar o arquivo ZIP do backup.');
            }
        }
    }

    /**
     * Coleta os caminhos de arquivos de upload que pertencem ao clube.
     * Retorna [path_relativo_ao_public_disk => caminho_absoluto].
     */
    private function collectUploadFiles(array $data): array
    {
        $files = [];

        $logo = $data['club']['logo'] ?? null;
        if ($logo) {
            $abs = Storage::disk('public')->path($logo);
            if (File::exists($abs)) {
                $files[$logo] = $abs;
            }
        }

        foreach ($data['desbravadores'] ?? [] as $d) {
            $foto = $d['foto'] ?? null;
            if ($foto) {
                $abs = Storage::disk('public')->path($foto);
                if (File::exists($abs)) {
                    $files[$foto] = $abs;
                }
            }
        }

        return $files;
    }

    /** Salva o ZIP nos discos de destino e retorna quais foram bem-sucedidos. */
    private function saveToDisks(string $tempZipPath, string $storagePath): array
    {
        $saved = [];

        foreach ($this->activeDisks() as $disk) {
            try {
                $stream = fopen($tempZipPath, 'rb');
                if ($stream === false) {
                    throw new \RuntimeException('Não foi possível abrir o ZIP para upload.');
                }
                try {
                    Storage::disk($disk)->put($storagePath, $stream);
                    $saved[] = $disk;
                } finally {
                    fclose($stream);
                }
            } catch (\Throwable $e) {
                Log::error("ClubBackupService: falha ao salvar no disco {$disk}: {$e->getMessage()}");
            }
        }

        if (empty($saved)) {
            throw new \RuntimeException('Nenhum disco de destino aceitou o backup do clube.');
        }

        return $saved;
    }

    /** Discos de destino ativos (local sempre; R2 somente se bucket configurado). */
    private function activeDisks(): array
    {
        $disks = ['local'];

        if (config('filesystems.disks.r2.bucket')) {
            $disks[] = 'r2';
        }

        return $disks;
    }
}

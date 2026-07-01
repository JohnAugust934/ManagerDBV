<?php

namespace App\Services;

use App\Models\Club;
use App\Support\TenantTables;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use ZipArchive;

/**
 * Restaura os dados de um clube a partir de um ZIP gerado pelo ClubBackupService.
 *
 * Garantias de isolamento:
 *  - Verifica que o manifest.json do backup corresponde ao clube alvo antes de
 *    qualquer operação destrutiva.
 *  - Deleta SOMENTE linhas com o club_id do clube alvo (nunca outros clubes).
 *  - Usa transação de BD: falha → rollback, nenhum dado parcialmente restaurado.
 *  - Arquivos de upload são restaurados APÓS commit do BD (operação reversível manualmente).
 *
 * Os IDs originais NÃO são preservados — o clube mantém o mesmo club_id, mas
 * os IDs de filhos (desbravadores, caixas etc.) são remapeados como em uma
 * importação, porque preservar IDs exigiria reset de sequences em PostgreSQL e
 * criaria colisões em MySQL. Para restore a isolamento é garantido pelo club_id.
 */
class ClubRestoreService
{
    private array $warnings = [];

    public function __construct(
        private readonly ClubImportService $importer
    ) {}

    /**
     * Restaura o clube a partir de um arquivo ZIP em um dos discos de backup.
     *
     * @throws \RuntimeException em caso de ZIP inválido, clube errado ou falha no BD.
     */
    public function restore(Club $club, string $disk, string $path): array
    {
        $tempZipPath = storage_path('app/temp-club-restore-'.$club->id.'-'.time().'.zip');
        $extractPath = storage_path('app/temp-club-restore-dir-'.$club->id.'-'.time());

        try {
            $this->copyToLocal($disk, $path, $tempZipPath);
            $this->extractSafely($tempZipPath, $extractPath);

            $manifest = $this->readManifest($extractPath);
            $this->verifyManifest($manifest, $club);

            $data = $this->readData($extractPath);

            $report = DB::transaction(function () use ($club, $data) {
                $this->deleteClubChildren($club->id);
                $this->updateClubRecord($club, $data['club'] ?? []);

                return $this->importer->importChildren($data, $club->id);
            });

            $this->restoreUploads($extractPath, $manifest);

            return array_merge($report, ['warnings' => $this->warnings]);

        } finally {
            @unlink($tempZipPath);
            File::deleteDirectory($extractPath);
        }
    }

    /** Extrai e valida o manifest.json. */
    private function readManifest(string $extractPath): array
    {
        $manifestPath = $extractPath.'/manifest.json';
        if (! File::exists($manifestPath)) {
            throw new \RuntimeException('Arquivo de backup inválido: manifest.json ausente.');
        }

        $manifest = json_decode(File::get($manifestPath), true);
        if (! is_array($manifest)) {
            throw new \RuntimeException('manifest.json corrompido ou inválido.');
        }

        return $manifest;
    }

    /** Verifica que o backup pertence ao clube alvo. */
    private function verifyManifest(array $manifest, Club $club): void
    {
        $backupClubId = $manifest['club_id'] ?? null;

        if ((int) $backupClubId !== $club->id) {
            throw new \RuntimeException(
                "Incompatibilidade: este backup é do clube #{$backupClubId}, ".
                "mas você está tentando restaurar o clube #{$club->id}. Operação cancelada."
            );
        }

        $version = $manifest['version'] ?? 0;
        if ($version < 1) {
            throw new \RuntimeException('Versão de backup incompatível.');
        }
    }

    /** Lê o data.json do backup. */
    private function readData(string $extractPath): array
    {
        $dataPath = $extractPath.'/data.json';
        if (! File::exists($dataPath)) {
            throw new \RuntimeException('Arquivo de backup inválido: data.json ausente.');
        }

        $data = json_decode(File::get($dataPath), true);
        if (! is_array($data)) {
            throw new \RuntimeException('data.json corrompido ou inválido.');
        }

        return $data;
    }

    /**
     * Deleta todos os dados filhos do clube em ordem reversa de dependência.
     * O registro na tabela `clubs` é preservado (apenas atualizado).
     */
    private function deleteClubChildren(int $clubId): void
    {
        // Pivôs/filhos SEM club_id próprio — via IDs do pai. Registro único em
        // App\Support\TenantTables (mesma lista da exclusão definitiva).
        foreach (TenantTables::childTables() as $table => $rel) {
            $parentIds = DB::table($rel['via'])->where('club_id', $clubId)->pluck('id');
            DB::table($table)->whereIn($rel['fk'], $parentIds)->delete();
        }

        // Tabelas com club_id direto, em ordem de dependência.
        foreach (TenantTables::clubIdTables() as $table) {
            DB::table($table)->where('club_id', $clubId)->delete();
        }

        // Usuários do clube (platform admin tem club_id null — não é afetado)
        DB::table('users')->where('club_id', $clubId)->delete();
    }

    /** Atualiza o registro do clube com os dados do backup (nome, cidade, associacao). */
    private function updateClubRecord(Club $club, array $backupClub): void
    {
        if (empty($backupClub)) {
            return;
        }

        $club->update([
            'nome' => $backupClub['nome'] ?? $club->nome,
            'cidade' => $backupClub['cidade'] ?? $club->cidade,
            'associacao' => $backupClub['associacao'] ?? $club->associacao,
        ]);
    }

    /** Restaura os arquivos de upload listados no manifest. */
    private function restoreUploads(string $extractPath, array $manifest): void
    {
        $uploadsDir = $extractPath.'/uploads';
        if (! File::isDirectory($uploadsDir)) {
            return;
        }

        $checksums = $manifest['upload_checksums'] ?? [];
        $restored = 0;
        $failed = 0;

        foreach ($checksums as $relativePath => $expectedChecksum) {
            $sourcePath = $uploadsDir.'/'.$relativePath;

            if (! File::exists($sourcePath)) {
                Log::warning("ClubRestoreService: upload ausente no backup: {$relativePath}");
                $failed++;

                continue;
            }

            $actualChecksum = hash_file('sha256', $sourcePath);
            if ($actualChecksum !== $expectedChecksum) {
                Log::warning("ClubRestoreService: checksum inválido para {$relativePath}");
                $failed++;

                continue;
            }

            try {
                $stream = fopen($sourcePath, 'rb');
                if ($stream !== false) {
                    Storage::disk('public')->put($relativePath, $stream);
                    fclose($stream);
                    $restored++;
                }
            } catch (\Throwable $e) {
                Log::error("ClubRestoreService: falha ao restaurar upload {$relativePath}: {$e->getMessage()}");
                $failed++;
            }
        }

        if ($failed > 0) {
            $this->warnings[] = "{$failed} arquivo(s) de upload não puderam ser restaurados.";
        }

        Log::info("ClubRestoreService: {$restored} upload(s) restaurados, {$failed} falha(s).");
    }

    private function copyToLocal(string $disk, string $path, string $tempZipPath): void
    {
        File::ensureDirectoryExists(dirname($tempZipPath));
        $source = Storage::disk($disk)->readStream($path);
        if (! is_resource($source)) {
            throw new \RuntimeException('Não foi possível ler o backup do disco de origem.');
        }

        $target = fopen($tempZipPath, 'wb');
        if ($target === false) {
            fclose($source);
            throw new \RuntimeException('Não foi possível criar arquivo temporário de extração.');
        }

        try {
            stream_copy_to_stream($source, $target);
        } finally {
            fclose($source);
            fclose($target);
        }
    }

    private function extractSafely(string $zipPath, string $extractPath): void
    {
        File::makeDirectory($extractPath, 0755, true, true);

        $zip = new ZipArchive;
        if ($zip->open($zipPath) !== true) {
            throw new \RuntimeException('O arquivo de backup está corrompido ou não é um ZIP válido.');
        }

        $ilegiveis = 0;

        try {
            for ($i = 0; $i < $zip->numFiles; $i++) {
                $entry = str_replace('\\', '/', $zip->getNameIndex($i));
                $entry = ltrim($entry, '/');

                if ($entry === '' || str_contains($entry, '../') || preg_match('/^[A-Za-z]:\//', $entry)) {
                    throw new \RuntimeException('Backup contém caminhos inválidos (possível path traversal).');
                }

                $dest = $extractPath.'/'.$entry;

                if (str_ends_with($entry, '/')) {
                    File::makeDirectory($dest, 0755, true, true);

                    continue;
                }

                File::makeDirectory(dirname($dest), 0755, true, true);
                $stream = $zip->getStream($entry);
                if (! $stream) {
                    // Entrada presente no indice mas ilegivel (corrompida). Pular em
                    // silencio mascararia uma restauracao incompleta como bem-sucedida.
                    Log::warning("ClubRestoreService: entrada ilegível no ZIP, ignorada: {$entry}");
                    $ilegiveis++;

                    continue;
                }

                $out = fopen($dest, 'wb');
                if ($out !== false) {
                    stream_copy_to_stream($stream, $out);
                    fclose($out);
                }
                fclose($stream);
            }
        } finally {
            $zip->close();
        }

        if ($ilegiveis > 0) {
            $this->warnings[] = "{$ilegiveis} entrada(s) do backup estavam ilegíveis e foram ignoradas na extração.";
        }
    }
}

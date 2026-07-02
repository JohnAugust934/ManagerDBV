<?php

namespace App\Support;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use ZipArchive;

/**
 * Extração de ZIP com proteção contra path traversal.
 *
 * Antes esta lógica existia DUPLICADA em BackupController::extractBackupArchiveSafely
 * e ClubRestoreService::extractSafely (Candidato D). Centralizar concentra a
 * verificação de caminhos maliciosos (`../`, caminho absoluto, drive Windows)
 * num único ponto testável.
 *
 * As duas variantes de comportamento são parametrizadas:
 *  - $requireNonEmpty: rejeita ZIP vazio (restauração de backup completo).
 *  - $tolerateUnreadable: entradas ilegíveis são puladas com aviso, em vez de
 *    lançar exceção (restauração de clube, que segue com um relatório de avisos).
 */
class SafeZipExtractor
{
    /** @var list<string> avisos acumulados na última extração. */
    private array $warnings = [];

    public function extract(string $zipPath, string $extractPath, bool $requireNonEmpty = false, bool $tolerateUnreadable = false): void
    {
        $this->warnings = [];

        File::makeDirectory($extractPath, 0755, true, true);

        $zip = new ZipArchive;
        if ($zip->open($zipPath) !== true) {
            throw new \RuntimeException('O arquivo de backup está corrompido ou não é um ZIP válido.');
        }

        $ilegiveis = 0;

        try {
            if ($requireNonEmpty && $zip->numFiles === 0) {
                throw new \RuntimeException('O arquivo ZIP está vazio.');
            }

            for ($index = 0; $index < $zip->numFiles; $index++) {
                // Nome original do índice preserva a chave real da entrada (usada em
                // getStream); a versão normalizada é só para checagem/destino.
                $original = (string) $zip->getNameIndex($index);
                $normalized = ltrim(str_replace('\\', '/', $original), '/');

                if ($normalized === '' || str_contains($normalized, '../') || preg_match('/^[A-Za-z]:\//', $normalized)) {
                    throw new \RuntimeException('Backup contém caminhos inválidos (possível path traversal).');
                }

                $dest = $extractPath.DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $normalized);

                if (str_ends_with($normalized, '/')) {
                    File::makeDirectory($dest, 0755, true, true);

                    continue;
                }

                File::makeDirectory(dirname($dest), 0755, true, true);

                $stream = $zip->getStream($original);
                if (! $stream) {
                    if ($tolerateUnreadable) {
                        // Pular em silêncio mascararia uma restauração incompleta como
                        // bem-sucedida — registramos e contamos para o relatório.
                        Log::warning("SafeZipExtractor: entrada ilegível no ZIP, ignorada: {$normalized}");
                        $ilegiveis++;

                        continue;
                    }

                    throw new \RuntimeException("Não foi possível ler o item '{$original}' do backup.");
                }

                $out = fopen($dest, 'wb');
                if ($out === false) {
                    fclose($stream);

                    throw new \RuntimeException("Não foi possível preparar o destino de extração para '{$original}'.");
                }

                stream_copy_to_stream($stream, $out);
                fclose($out);
                fclose($stream);
            }
        } finally {
            $zip->close();
        }

        if ($ilegiveis > 0) {
            $this->warnings[] = "{$ilegiveis} entrada(s) do backup estavam ilegíveis e foram ignoradas na extração.";
        }
    }

    /**
     * Avisos acumulados na última extração (ex.: entradas ilegíveis toleradas).
     *
     * @return list<string>
     */
    public function warnings(): array
    {
        return $this->warnings;
    }
}

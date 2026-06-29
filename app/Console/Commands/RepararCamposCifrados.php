<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

/**
 * Normaliza campos sensíveis de desbravadores para o formato esperado pelo cast
 * 'encrypted' do Eloquent (Crypt::encryptString/decryptString, SEM serialização).
 *
 * Conserta dois defeitos introduzidos por importações antigas (ClubImportService):
 *   1) numero_sus gravado em PLAINTEXT (campo não era recriptografado) — quebrava
 *      o export/backup com "The payload is invalid." ao ler pelo cast.
 *   2) rg/alergias/medicamentos_continuos/plano_saude cifrados com o helper
 *      encrypt() (que SERIALIZA) — ao ler pelo cast (que NÃO desserializa) o valor
 *      voltava com um wrapper "s:N:\"...\";" (corrupção silenciosa).
 *
 * Lê o valor BRUTO via DB::table (sem cast) e reescreve com encryptString quando
 * necessário. Idempotente: valores já no formato correto são deixados intactos.
 *
 * Uso:
 *   php artisan desbravadores:reparar-cifrados --dry-run
 *   php artisan desbravadores:reparar-cifrados
 */
class RepararCamposCifrados extends Command
{
    protected $signature = 'desbravadores:reparar-cifrados {--dry-run : Apenas relata, não grava}';

    protected $description = 'Normaliza campos sensíveis de desbravadores para o formato do cast encrypted (corrige "The payload is invalid." e wrappers serializados)';

    /** Campos com cast 'encrypted' em App\Models\Desbravador (cpf é tratado pelo mutator com serialização própria). */
    private const CAMPOS = ['rg', 'numero_sus', 'alergias', 'medicamentos_continuos', 'plano_saude'];

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $reparados = 0;
        $linhas = 0;

        DB::table('desbravadores')->orderBy('id')->chunkById(200, function ($rows) use (&$reparados, &$linhas, $dryRun) {
            foreach ($rows as $d) {
                $update = [];

                foreach (self::CAMPOS as $campo) {
                    $valor = $d->{$campo} ?? null;
                    if ($valor === null || $valor === '') {
                        continue;
                    }

                    $novo = $this->normalizar((string) $valor);
                    if ($novo !== null) {
                        $update[$campo] = $novo;
                    }
                }

                if ($update !== []) {
                    $linhas++;
                    $reparados += count($update);
                    $this->line("Desbravador #{$d->id}: ".implode(', ', array_keys($update)));

                    if (! $dryRun) {
                        DB::table('desbravadores')->where('id', $d->id)->update($update);
                    }
                }
            }
        });

        $this->newLine();
        $modo = $dryRun ? '[dry-run] ' : '';
        $this->info("{$modo}{$linhas} desbravador(es), {$reparados} campo(s) normalizado(s).");

        return self::SUCCESS;
    }

    /**
     * Retorna o novo valor cifrado (encryptString) se o armazenado precisar de conserto,
     * ou null se já estiver no formato correto.
     */
    private function normalizar(string $valor): ?string
    {
        // 1) Plaintext puro: decryptString lança DecryptException.
        try {
            $dec = Crypt::decryptString($valor);
        } catch (DecryptException) {
            return Crypt::encryptString($valor);
        }

        // 2) Cifrado válido. Se o conteúdo for um wrapper serializado pelo helper
        //    encrypt() (ex.: 's:12:"...";'), o valor real é o desserializado.
        $plain = $this->desserializarSeForWrapper($dec);
        if ($plain !== null) {
            return Crypt::encryptString($plain);
        }

        // Já está no formato esperado pelo cast.
        return null;
    }

    /** Detecta e desserializa um wrapper de string PHP serializada; null se não for. */
    private function desserializarSeForWrapper(string $valor): ?string
    {
        if (! preg_match('/^s:\d+:"/', $valor)) {
            return null;
        }

        try {
            $un = @unserialize($valor);
        } catch (\Throwable) {
            return null;
        }

        return is_string($un) ? $un : null;
    }
}

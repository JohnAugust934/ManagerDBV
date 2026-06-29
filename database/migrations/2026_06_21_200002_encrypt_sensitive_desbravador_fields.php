<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Criptografa campos sensíveis de desbravadores em repouso (LGPD Art. 46).
 *
 * CPF e RG usam o cast 'encrypted' do Laravel (AES-256-CBC via APP_KEY).
 * Como o cast é não-determinístico (cada encrypt gera ciphertext diferente),
 * a unique constraint (club_id, cpf) é substituída por (club_id, cpf_hash),
 * onde cpf_hash = SHA-256 do CPF numérico — determinístico e sem revelar o
 * valor original (não é reversível sem o CPF completo).
 *
 * Campos médicos (alergias, medicamentos_continuos, plano_saude) e rg também
 * são criptografados; não têm restrição de unicidade, apenas cast adicionado.
 *
 * Compatibilidade com o upgrade multi-tenant (UPGRADE-MULTITENANT.md):
 *  - Migration idempotente: pula backfill de linhas que já têm cpf_hash.
 *  - Linhas sem CPF (NULL) ficam com cpf_hash NULL — a unique permite NULLs duplicados.
 */
return new class extends Migration
{
    public function up(): void
    {
        // 1. Remover a unique (club_id, cpf) ANTES de mudar cpf para TEXT.
        //    No MySQL, dar MODIFY numa coluna que ainda integra um índice para
        //    TEXT lança ERROR 1170 (BLOB/TEXT em chave sem prefixo de tamanho).
        //    Dropar o índice primeiro deixa a coluna livre para a mudança de tipo.
        Schema::table('desbravadores', function (Blueprint $table) {
            $table->dropUnique('desbravadores_club_cpf_unique');
        });

        // 2. Adicionar cpf_hash e alargar as colunas string para TEXT
        //    (valores criptografados têm ~200+ chars vs 14 do CPF bruto).
        Schema::table('desbravadores', function (Blueprint $table) {
            $table->char('cpf_hash', 64)->nullable()->after('cpf');
            // SQLite aceita TEXT e VARCHAR como equivalentes, mas para MySQL/PG
            // precisamos mudar o tipo. Usamos text() que é idempotente no SQLite.
            $table->text('cpf')->nullable()->change();
            $table->text('rg')->nullable()->change();
            // numero_sus era VARCHAR(255): o ciphertext pode passar de 255 e
            // truncar — alargamos para TEXT como os demais campos cifrados.
            $table->text('numero_sus')->nullable()->change();
        });

        // 3. Backfill: computar hash e criptografar apenas linhas ainda em plaintext.
        //    Identificamos plaintext porque cpf_hash ainda é NULL.
        DB::table('desbravadores')
            ->whereNotNull('cpf')
            ->whereNull('cpf_hash')
            ->chunkById(200, function ($rows) {
                foreach ($rows as $row) {
                    $cpfNumerico = preg_replace('/\D/', '', $row->cpf);
                    DB::table('desbravadores')->where('id', $row->id)->update([
                        'cpf_hash' => hash('sha256', $cpfNumerico),
                        'cpf' => encrypt($row->cpf),
                    ]);
                }
            });

        // 4. Criptografar RG (sem unique — não precisa de hash separado).
        DB::table('desbravadores')
            ->whereNotNull('rg')
            ->chunkById(200, function ($rows) {
                foreach ($rows as $row) {
                    if (! $this->jaEstaEncriptado($row->rg)) {
                        DB::table('desbravadores')->where('id', $row->id)->update([
                            'rg' => encrypt($row->rg),
                        ]);
                    }
                }
            });

        // 5. Criptografar campos médicos + numero_sus (todos TEXT — apenas encrypt).
        foreach (['numero_sus', 'alergias', 'medicamentos_continuos', 'plano_saude'] as $field) {
            DB::table('desbravadores')
                ->whereNotNull($field)
                ->chunkById(200, function ($rows) use ($field) {
                    foreach ($rows as $row) {
                        $value = $row->{$field};
                        if (! $this->jaEstaEncriptado($value)) {
                            DB::table('desbravadores')->where('id', $row->id)->update([
                                $field => encrypt($value),
                            ]);
                        }
                    }
                });
        }

        // 6. Criar a unique baseada em cpf_hash (a antiga em cpf já foi removida
        //    no passo 1). cpf_hash já está preenchido pelo backfill acima.
        Schema::table('desbravadores', function (Blueprint $table) {
            $table->unique(['club_id', 'cpf_hash'], 'desbravadores_club_cpf_hash_unique');
        });
    }

    /**
     * Verifica se um valor já está criptografado pelo Laravel (tentando decrypt).
     * Mais confiável que checar comprimento: funciona para qualquer tamanho de texto.
     */
    private function jaEstaEncriptado(string $value): bool
    {
        try {
            decrypt($value);

            return true;
        } catch (\Illuminate\Contracts\Encryption\DecryptException) {
            return false;
        }
    }

    public function down(): void
    {
        // Este rollback DECIFRA todo o PII (CPF/RG/SUS/campos médicos) para texto
        // puro. Um migrate:rollback acidental em produção exporia tudo. Exige
        // override explícito (ALLOW_PII_ROLLBACK=1) — defina apenas após backup.
        if (app()->isProduction() && ! env('ALLOW_PII_ROLLBACK')) {
            throw new \RuntimeException(
                'Rollback bloqueado: decifra PII em repouso. Faça backup e defina ALLOW_PII_ROLLBACK=1 para prosseguir.'
            );
        }

        // Reverter a unique
        Schema::table('desbravadores', function (Blueprint $table) {
            $table->dropUnique('desbravadores_club_cpf_hash_unique');
        });

        // Descriptografar os campos (necessário para recriar a unique em cpf)
        DB::table('desbravadores')
            ->whereNotNull('cpf_hash')
            ->chunkById(200, function ($rows) {
                foreach ($rows as $row) {
                    try {
                        DB::table('desbravadores')->where('id', $row->id)->update([
                            'cpf' => decrypt($row->cpf),
                            'rg' => $row->rg ? decrypt($row->rg) : null,
                            'cpf_hash' => null,
                        ]);
                    } catch (\Illuminate\Contracts\Encryption\DecryptException $e) {
                        \Illuminate\Support\Facades\Log::warning(
                            "migrate:rollback — falha ao descriptografar desbravador #{$row->id}: ".$e->getMessage()
                        );
                    }
                }
            });

        foreach (['numero_sus', 'alergias', 'medicamentos_continuos', 'plano_saude'] as $field) {
            DB::table('desbravadores')
                ->whereNotNull($field)
                ->chunkById(200, function ($rows) use ($field) {
                    foreach ($rows as $row) {
                        try {
                            DB::table('desbravadores')->where('id', $row->id)->update([
                                $field => decrypt($row->{$field}),
                            ]);
                        } catch (\Illuminate\Contracts\Encryption\DecryptException $e) {
                            \Illuminate\Support\Facades\Log::warning(
                                "migrate:rollback — falha ao descriptografar {$field} do desbravador #{$row->id}: ".$e->getMessage()
                            );
                        }
                    }
                });
        }

        Schema::table('desbravadores', function (Blueprint $table) {
            $table->dropColumn('cpf_hash');
            $table->string('cpf', 14)->nullable()->change();
            $table->string('rg', 20)->nullable()->change();
            $table->string('numero_sus')->nullable()->change();
            $table->unique(['club_id', 'cpf'], 'desbravadores_club_cpf_unique');
        });
    }
};

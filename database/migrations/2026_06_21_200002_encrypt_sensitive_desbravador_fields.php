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
        // 1. Adicionar cpf_hash e alargar as colunas string para TEXT
        //    (valores criptografados têm ~200+ chars vs 14 do CPF bruto).
        Schema::table('desbravadores', function (Blueprint $table) {
            $table->char('cpf_hash', 64)->nullable()->after('cpf');
            // SQLite aceita TEXT e VARCHAR como equivalentes, mas para MySQL/PG
            // precisamos mudar o tipo. Usamos text() que é idempotente no SQLite.
            $table->text('cpf')->nullable()->change();
            $table->text('rg')->nullable()->change();
        });

        // 2. Backfill: computar hash e criptografar apenas linhas ainda em plaintext.
        //    Identificamos plaintext porque cpf_hash ainda é NULL.
        DB::table('desbravadores')
            ->whereNotNull('cpf')
            ->whereNull('cpf_hash')
            ->chunkById(200, function ($rows) {
                foreach ($rows as $row) {
                    $cpfNumerico = preg_replace('/\D/', '', $row->cpf);
                    DB::table('desbravadores')->where('id', $row->id)->update([
                        'cpf_hash'  => hash('sha256', $cpfNumerico),
                        'cpf'       => encrypt($row->cpf),
                    ]);
                }
            });

        // 3. Criptografar RG (sem unique — não precisa de hash separado).
        DB::table('desbravadores')
            ->whereNotNull('rg')
            ->chunkById(200, function ($rows) {
                foreach ($rows as $row) {
                    // Identifica valores já criptografados: o Laravel usa base64+json
                    // — é muito mais longo que um RG real (até ~20 chars).
                    if (strlen($row->rg) <= 30) {
                        DB::table('desbravadores')->where('id', $row->id)->update([
                            'rg' => encrypt($row->rg),
                        ]);
                    }
                }
            });

        // 4. Criptografar campos médicos (já são TEXT — apenas encrypt).
        foreach (['alergias', 'medicamentos_continuos', 'plano_saude'] as $field) {
            DB::table('desbravadores')
                ->whereNotNull($field)
                ->chunkById(200, function ($rows) use ($field) {
                    foreach ($rows as $row) {
                        $value = $row->{$field};
                        // Pula valores que já parecem criptografados (> 80 chars base64).
                        if (strlen($value) <= 80) {
                            DB::table('desbravadores')->where('id', $row->id)->update([
                                $field => encrypt($value),
                            ]);
                        }
                    }
                });
        }

        // 5. Substituir a unique constraint de CPF por uma baseada em cpf_hash.
        Schema::table('desbravadores', function (Blueprint $table) {
            $table->dropUnique('desbravadores_club_cpf_unique');
            $table->unique(['club_id', 'cpf_hash'], 'desbravadores_club_cpf_hash_unique');
        });
    }

    public function down(): void
    {
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
                            'cpf'      => decrypt($row->cpf),
                            'rg'       => $row->rg ? decrypt($row->rg) : null,
                            'cpf_hash' => null,
                        ]);
                    } catch (\Exception) {
                        // Se a chave de decrypt falhou, mantém como está
                    }
                }
            });

        foreach (['alergias', 'medicamentos_continuos', 'plano_saude'] as $field) {
            DB::table('desbravadores')
                ->whereNotNull($field)
                ->chunkById(200, function ($rows) use ($field) {
                    foreach ($rows as $row) {
                        try {
                            DB::table('desbravadores')->where('id', $row->id)->update([
                                $field => decrypt($row->{$field}),
                            ]);
                        } catch (\Exception) {
                        }
                    }
                });
        }

        Schema::table('desbravadores', function (Blueprint $table) {
            $table->dropColumn('cpf_hash');
            $table->string('cpf', 14)->nullable()->change();
            $table->string('rg', 20)->nullable()->change();
            $table->unique(['club_id', 'cpf'], 'desbravadores_club_cpf_unique');
        });
    }
};

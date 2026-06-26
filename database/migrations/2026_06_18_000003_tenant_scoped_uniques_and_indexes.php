<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Fase 3 da reestruturação multi-tenant: uniques e índices escopados por tenant.
 *
 *  - desbravadores.cpf passa a ser único POR CLUBE (antes era único global na
 *    validação — impedia a mesma pessoa de existir em dois clubes). cpf nulo é
 *    permitido em duplicidade (NULLs distintos no índice unique nos três bancos).
 *  - Índices avulsos de club_id viram compostos `(club_id, <coluna quente>)`, que
 *    também servem as consultas que filtram só por club_id (coluna líder). Os
 *    compostos são criados ANTES de remover o índice avulso de club_id — em MySQL
 *    a FK precisa de um índice com club_id na frente o tempo todo.
 */
return new class extends Migration
{
    public function up(): void
    {
        // Pré-validação antes de qualquer DDL: a unique(club_id, cpf) falharia em
        // dados com CPF duplicado dentro de um mesmo clube. Aborta limpo (em MySQL
        // o DDL não é transacional e quebraria a migration pela metade).
        $this->abortarSeCpfDuplicado();

        // desbravadores: CPF único por clube + (club_id, ativo).
        Schema::table('desbravadores', function (Blueprint $table) {
            $table->unique(['club_id', 'cpf'], 'desbravadores_club_cpf_unique');
            $table->index(['club_id', 'ativo'], 'desbravadores_club_ativo_index');
        });
        Schema::table('desbravadores', function (Blueprint $table) {
            $table->dropIndex('desbravadores_club_id_index');
        });

        // frequencias: (club_id, data).
        Schema::table('frequencias', function (Blueprint $table) {
            $table->index(['club_id', 'data'], 'frequencias_club_data_index');
        });
        Schema::table('frequencias', function (Blueprint $table) {
            $table->dropIndex('frequencias_club_id_index');
        });

        // mensalidades: (club_id, status) e (club_id, mes, ano).
        Schema::table('mensalidades', function (Blueprint $table) {
            $table->index(['club_id', 'status'], 'mensalidades_club_status_index');
            $table->index(['club_id', 'mes', 'ano'], 'mensalidades_club_mes_ano_index');
        });
        Schema::table('mensalidades', function (Blueprint $table) {
            $table->dropIndex('mensalidades_club_id_index');
        });

        // caixas: (club_id, data_movimentacao).
        Schema::table('caixas', function (Blueprint $table) {
            $table->index(['club_id', 'data_movimentacao'], 'caixas_club_data_index');
        });
        Schema::table('caixas', function (Blueprint $table) {
            $table->dropIndex('caixas_club_id_index');
        });

        // eventos: (club_id, data_inicio).
        Schema::table('eventos', function (Blueprint $table) {
            $table->index(['club_id', 'data_inicio'], 'eventos_club_data_index');
        });
        Schema::table('eventos', function (Blueprint $table) {
            $table->dropIndex('eventos_club_id_index');
        });
    }

    public function down(): void
    {
        // Recria os índices avulsos ANTES de remover os compostos (FK em MySQL).
        Schema::table('desbravadores', function (Blueprint $table) {
            $table->index('club_id', 'desbravadores_club_id_index');
        });
        Schema::table('desbravadores', function (Blueprint $table) {
            $table->dropUnique('desbravadores_club_cpf_unique');
            $table->dropIndex('desbravadores_club_ativo_index');
        });

        Schema::table('frequencias', function (Blueprint $table) {
            $table->index('club_id', 'frequencias_club_id_index');
        });
        Schema::table('frequencias', function (Blueprint $table) {
            $table->dropIndex('frequencias_club_data_index');
        });

        Schema::table('mensalidades', function (Blueprint $table) {
            $table->index('club_id', 'mensalidades_club_id_index');
        });
        Schema::table('mensalidades', function (Blueprint $table) {
            $table->dropIndex('mensalidades_club_status_index');
            $table->dropIndex('mensalidades_club_mes_ano_index');
        });

        Schema::table('caixas', function (Blueprint $table) {
            $table->index('club_id', 'caixas_club_id_index');
        });
        Schema::table('caixas', function (Blueprint $table) {
            $table->dropIndex('caixas_club_data_index');
        });

        Schema::table('eventos', function (Blueprint $table) {
            $table->index('club_id', 'eventos_club_id_index');
        });
        Schema::table('eventos', function (Blueprint $table) {
            $table->dropIndex('eventos_club_data_index');
        });
    }

    /**
     * Aborta se houver CPF duplicado DENTRO de um mesmo clube — o que faria a
     * criação de unique(club_id, cpf) falhar. CPF nulo é ignorado (NULLs distintos).
     */
    private function abortarSeCpfDuplicado(): void
    {
        $duplicados = DB::table('desbravadores')
            ->select('club_id', 'cpf', DB::raw('count(*) as total'))
            ->whereNotNull('cpf')
            ->groupBy('club_id', 'cpf')
            ->havingRaw('count(*) > 1')
            ->get();

        if ($duplicados->isNotEmpty()) {
            $exemplo = $duplicados->first();

            throw new RuntimeException(
                "Não é possível criar unique(club_id, cpf): há {$duplicados->count()} CPF(s) ".
                "duplicado(s) dentro de um mesmo clube (ex.: clube #{$exemplo->club_id}, cpf {$exemplo->cpf}). ".
                'Resolva os duplicados antes de migrar.'
            );
        }
    }
};

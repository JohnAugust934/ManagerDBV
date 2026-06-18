<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Verifica a integridade do isolamento multi-tenant — a rede de proteção
 * AGNÓSTICA DE BANCO que cobre o ponto cego do SQLite (que não enforça FK
 * adicionada via ALTER) e valida invariantes que nenhuma FK garante sozinha.
 *
 * Pensado para ser GATE de CI e de deploy: retorna FAILURE (exit 1) se achar
 * qualquer problema. Detecta — não corrige (a correção é papel do
 * `tenant:upgrade-legacy` e das migrations de backfill).
 *
 * Invariantes checadas (schema atual, pré-desnormalização da Fase 1):
 *  - Tabela com club_id direto: nenhuma linha pode ter club_id NULL (ficaria
 *    invisível sob fail-closed do ClubScope) nem apontar para clube inexistente.
 *  - Usuário comum (não platform admin) sem clube ficaria travado pelo fail-closed.
 *  - Desbravador sem unidade não tem vínculo de clube (hoje o tenant vem da
 *    unidade) → invisível. Na Fase 1, quando `desbravadores` ganhar club_id
 *    direto, mover essa tabela para TABELAS_COM_CLUB_ID e remover esta checagem.
 */
class CheckTenantIntegrity extends Command
{
    protected $signature = 'tenant:check-integrity {--json : Saída em JSON (para consumo em CI)}';

    protected $description = 'Verifica a integridade do isolamento multi-tenant (órfãos, club_id pendente, vínculos quebrados)';

    /**
     * Tabelas com coluna club_id direta. Toda linha DEVE ter um club_id válido.
     * Estender conforme a desnormalização da Fase 1 (desbravadores, frequencias,
     * mensalidades e pivôs ganham club_id direto).
     *
     * @var list<string>
     */
    /** Tabelas onde club_id é OBRIGATÓRIO (não pode ser nulo nem pendente). */
    private const TABELAS_COM_CLUB_ID = [
        'unidades',
        'desbravadores',
        'frequencias',
        'mensalidades',
        'caixas',
        'patrimonios',
        'eventos',
        'atas',
        'atos',
        'attendance_columns',
        'ranking_snapshots',
    ];

    /**
     * Tabelas onde club_id pode ser nulo (estado legítimo), mas se preenchido
     * precisa apontar para um clube existente. `invitations`: convite de
     * bootstrap (primeiro diretor) é criado sem clube.
     */
    private const TABELAS_CLUB_ID_OPCIONAL = [
        'invitations',
    ];

    public function handle(): int
    {
        $problemas = [];

        foreach (self::TABELAS_COM_CLUB_ID as $tabela) {
            $nulos = DB::table($tabela)->whereNull('club_id')->count();
            if ($nulos > 0) {
                $problemas[] = ['club_id nulo (invisível ao clube)', $tabela, $nulos];
            }

            $pendentes = $this->contarClubIdPendente($tabela);
            if ($pendentes > 0) {
                $problemas[] = ['club_id aponta para clube inexistente', $tabela, $pendentes];
            }
        }

        // Tabelas onde o nulo é permitido: só checamos club_id pendente.
        foreach (self::TABELAS_CLUB_ID_OPCIONAL as $tabela) {
            $pendentes = $this->contarClubIdPendente($tabela);
            if ($pendentes > 0) {
                $problemas[] = ['club_id aponta para clube inexistente', $tabela, $pendentes];
            }
        }

        // Usuários comuns sem clube ficam travados pelo fail-closed (platform
        // admins legitimamente têm club_id = null e são ignorados aqui).
        $usuariosOrfaos = DB::table('users')
            ->whereNull('club_id')
            ->where('is_platform_admin', false)
            ->count();
        if ($usuariosOrfaos > 0) {
            $problemas[] = ['usuário comum sem clube (login travado pelo fail-closed)', 'users', $usuariosOrfaos];
        }

        $usuariosClubePendente = $this->contarClubIdPendente('users');
        if ($usuariosClubePendente > 0) {
            $problemas[] = ['club_id aponta para clube inexistente', 'users', $usuariosClubePendente];
        }

        // Desbravador sem unidade não tem como herdar o clube (vínculo indireto).
        $semUnidade = DB::table('desbravadores')->whereNull('unidade_id')->count();
        if ($semUnidade > 0) {
            $problemas[] = ['desbravador sem unidade (sem vínculo de clube)', 'desbravadores', $semUnidade];
        }

        // Desbravador apontando para unidade inexistente (FK lógica quebrada).
        $unidadePendente = DB::table('desbravadores')
            ->whereNotNull('unidade_id')
            ->whereNotIn('unidade_id', fn ($q) => $q->from('unidades')->select('id'))
            ->count();
        if ($unidadePendente > 0) {
            $problemas[] = ['unidade_id aponta para unidade inexistente', 'desbravadores', $unidadePendente];
        }

        // Divergência pai/filho: o club_id desnormalizado precisa bater com o do pai.
        $this->checarDivergencia(
            $problemas,
            'desbravadores', 'unidades', 'unidade_id',
            'club_id do desbravador difere do da unidade',
        );
        $this->checarDivergencia(
            $problemas,
            'frequencias', 'desbravadores', 'desbravador_id',
            'club_id da frequência difere do do desbravador',
        );
        $this->checarDivergencia(
            $problemas,
            'mensalidades', 'desbravadores', 'desbravador_id',
            'club_id da mensalidade difere do do desbravador',
        );

        // Pivôs sem club_id próprio (Fase 1/4): garantir que ligam entidades do
        // MESMO clube — uma inscrição entre clubes diferentes seria vazamento.
        $this->checarPivotMesmoClube(
            $problemas,
            'desbravador_evento', 'desbravadores', 'desbravador_id', 'eventos', 'evento_id',
            'inscrição liga desbravador e evento de clubes diferentes',
        );
        $this->checarPivotMesmoClube(
            $problemas,
            'frequencia_column_values', 'frequencias', 'frequencia_id', 'attendance_columns', 'attendance_column_id',
            'valor de coluna liga frequência e coluna de clubes diferentes',
        );

        return $this->reportar($problemas);
    }

    /**
     * Conta linhas cujo club_id não-nulo não existe na tabela clubs.
     * Com clubs vazia, todo club_id é considerado pendente (NOT IN conjunto vazio).
     */
    private function contarClubIdPendente(string $tabela): int
    {
        return DB::table($tabela)
            ->whereNotNull('club_id')
            ->whereNotIn('club_id', fn ($q) => $q->from('clubs')->select('id'))
            ->count();
    }

    /**
     * Conta linhas da tabela filha cujo club_id desnormalizado diverge do club_id
     * do registro-pai (ambos não-nulos). Invariante da Fase 1.
     *
     * @param  list<array{0:string,1:string,2:int}>  $problemas
     */
    private function checarDivergencia(array &$problemas, string $filha, string $pai, string $fk, string $descricao): void
    {
        $divergentes = DB::table($filha)
            ->join($pai, "{$pai}.id", '=', "{$filha}.{$fk}")
            ->whereNotNull("{$filha}.club_id")
            ->whereNotNull("{$pai}.club_id")
            ->whereColumn("{$filha}.club_id", '!=', "{$pai}.club_id")
            ->count();

        if ($divergentes > 0) {
            $problemas[] = [$descricao, $filha, $divergentes];
        }
    }

    /**
     * Conta linhas de um pivô (sem club_id próprio) que ligam dois pais de clubes
     * diferentes — vazamento cross-tenant. Os dois pais têm club_id direto.
     *
     * @param  list<array{0:string,1:string,2:int}>  $problemas
     */
    private function checarPivotMesmoClube(
        array &$problemas,
        string $pivot,
        string $paiA,
        string $fkA,
        string $paiB,
        string $fkB,
        string $descricao,
    ): void {
        $divergentes = DB::table("{$pivot} as p")
            ->join("{$paiA} as a", 'a.id', '=', "p.{$fkA}")
            ->join("{$paiB} as b", 'b.id', '=', "p.{$fkB}")
            ->whereColumn('a.club_id', '!=', 'b.club_id')
            ->count();

        if ($divergentes > 0) {
            $problemas[] = [$descricao, $pivot, $divergentes];
        }
    }

    /**
     * @param  list<array{0:string,1:string,2:int}>  $problemas
     */
    private function reportar(array $problemas): int
    {
        if ($this->option('json')) {
            $this->line(json_encode([
                'ok' => $problemas === [],
                'total' => array_sum(array_column($problemas, 2)),
                'problemas' => array_map(
                    fn ($p) => ['descricao' => $p[0], 'tabela' => $p[1], 'linhas' => $p[2]],
                    $problemas,
                ),
            ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));

            return $problemas === [] ? self::SUCCESS : self::FAILURE;
        }

        if ($problemas === []) {
            $this->info('✅ Integridade multi-tenant OK — nenhum problema encontrado.');

            return self::SUCCESS;
        }

        $this->error('❌ Problemas de integridade multi-tenant encontrados:');
        $this->table(['Problema', 'Tabela', 'Linhas'], $problemas);
        $this->newLine();
        $this->warn('Corrija antes de prosseguir. Em banco legado, rode `php artisan tenant:upgrade-legacy`.');

        return self::FAILURE;
    }
}

<?php

namespace App\Console\Commands;

use App\Models\Club;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Migra um banco da versão single-tenant (v4.0.0-beta) para o modelo multi-tenant,
 * SEM perda de dados.
 *
 * O que faz (idempotente, dentro de uma transação):
 *  - Resolve o clube alvo (o único existente, ou --club=ID).
 *  - Faz backfill de club_id = <clube> em TODAS as tabelas escopadas que tiverem
 *    linhas órfãs (club_id NULL). Sob fail-closed, linhas sem club_id ficariam
 *    invisíveis — este passo garante que continuem visíveis.
 *  - Define o(s) platform admin(s) via --platform-admin=email (club_id=NULL,
 *    is_platform_admin=true) — necessário para acessar /platform e os backups.
 *  - Vincula ao clube quaisquer usuários órfãos (club_id NULL) que NÃO foram
 *    marcados como platform admin (senão ficariam travados pelo fail-closed).
 *
 * Pré-requisito: rodar `php artisan migrate --force` ANTES (schema novo) e
 * SEMPRE `php artisan backup:run` antes de tudo.
 */
class UpgradeLegacyTenant extends Command
{
    protected $signature = 'tenant:upgrade-legacy
        {--club= : ID do clube alvo (default: o único clube existente)}
        {--platform-admin=* : E-mail(s) a promover a platform admin (cross-tenant)}
        {--dry-run : Apenas relata o que seria feito, sem gravar}';

    protected $description = 'Migra um banco single-tenant (v4.0.0-beta) para multi-tenant sem perda de dados';

    /** Tabelas com coluna club_id direta que recebem backfill. */
    private const TABELAS_COM_CLUB_ID = [
        'unidades',
        'caixas',
        'patrimonios',
        'eventos',
        'atas',
        'atos',
        'attendance_columns',
        'invitations',
        'ranking_snapshots',
    ];

    public function handle(): int
    {
        if (! Schema::hasColumn('users', 'is_platform_admin')) {
            $this->error('Schema multi-tenant ausente. Rode primeiro: php artisan migrate --force');

            return self::FAILURE;
        }

        $dryRun = (bool) $this->option('dry-run');

        $club = $this->resolveClub();
        if (! $club) {
            return self::FAILURE;
        }

        $this->info("Clube alvo: #{$club->id} — {$club->nome}".($dryRun ? '  [DRY-RUN]' : ''));
        $this->newLine();

        // 1) Relatório de órfãos (antes)
        $orfaos = $this->contarOrfaos();
        $this->table(['Tabela', 'Linhas com club_id NULL'], collect($orfaos)
            ->map(fn ($qtd, $tabela) => [$tabela, $qtd])
            ->values()
            ->all());

        $this->avisarDesbravadoresSemUnidade();

        // 2) Platform admins solicitados
        $platformAdmins = $this->resolverPlatformAdmins();

        // Usuários órfãos a vincular ao clube: nunca inclui platform admins (nem os
        // já existentes, nem os que serão promovidos agora) — eles são cross-tenant.
        $usuariosOrfaos = User::query()->whereNull('club_id')
            ->where('is_platform_admin', false)
            ->whereNotIn('email', $platformAdmins->pluck('email')->all())
            ->get();

        $this->newLine();
        $this->line('Plano de ação:');
        $this->line('  • Backfill de club_id = '.$club->id.' nas tabelas acima ('.array_sum($orfaos).' linhas).');
        $this->line('  • Promover a platform admin: '.($platformAdmins->isEmpty() ? '(nenhum)' : $platformAdmins->pluck('email')->implode(', ')));
        $this->line('  • Vincular ao clube '.$usuariosOrfaos->count().' usuário(s) órfão(s) não-platform-admin.');

        if ($platformAdmins->isEmpty() && ! $this->jaExistePlatformAdmin()) {
            $this->warn('Nenhum platform admin definido nem existente. Sem ele, NINGUÉM acessará /platform nem os backups.');
            $this->warn('Considere: --platform-admin=email@do-super-admin.com');
        }

        if ($dryRun) {
            $this->newLine();
            $this->info('DRY-RUN: nada foi gravado.');

            return self::SUCCESS;
        }

        DB::transaction(function () use ($club, $platformAdmins, $usuariosOrfaos) {
            // Backfill das tabelas escopadas (DB direto: ignora scopes e events).
            foreach (self::TABELAS_COM_CLUB_ID as $tabela) {
                DB::table($tabela)->whereNull('club_id')->update(['club_id' => $club->id]);
            }

            // Platform admins (cross-tenant): sem clube, flag ligada.
            foreach ($platformAdmins as $admin) {
                $admin->forceFill([
                    'is_platform_admin' => true,
                    'club_id' => null,
                ])->save();
            }

            // Usuários órfãos restantes entram no clube (senão ficam travados).
            foreach ($usuariosOrfaos as $usuario) {
                $usuario->forceFill(['club_id' => $club->id])->save();
            }
        });

        $this->newLine();
        $this->info('✅ Migração concluída. Resumo final:');
        $this->table(['Tabela', 'Linhas com club_id NULL (restantes)'], collect($this->contarOrfaos())
            ->map(fn ($qtd, $tabela) => [$tabela, $qtd])
            ->values()
            ->all());

        $this->newLine();
        $this->info('Próximos passos: limpar caches (config:cache, route:cache) e validar login do platform admin e de um master de clube.');

        return self::SUCCESS;
    }

    private function resolveClub(): ?Club
    {
        $clubId = $this->option('club');

        if ($clubId !== null) {
            $club = Club::find((int) $clubId);
            if (! $club) {
                $this->error("Clube #{$clubId} não encontrado.");

                return null;
            }

            return $club;
        }

        $total = Club::count();

        if ($total === 0) {
            $this->error('Nenhum clube encontrado no banco. Nada a migrar.');

            return null;
        }

        if ($total > 1) {
            $this->error("Há {$total} clubes no banco. Especifique o alvo com --club=ID.");
            $this->table(['ID', 'Nome'], Club::orderBy('id')->get(['id', 'nome'])->map(fn ($c) => [$c->id, $c->nome])->all());

            return null;
        }

        return Club::first();
    }

    /** @return array<string,int> */
    private function contarOrfaos(): array
    {
        $contagem = [];
        foreach (self::TABELAS_COM_CLUB_ID as $tabela) {
            $contagem[$tabela] = DB::table($tabela)->whereNull('club_id')->count();
        }

        return $contagem;
    }

    private function avisarDesbravadoresSemUnidade(): void
    {
        $semUnidade = DB::table('desbravadores')->whereNull('unidade_id')->count();
        if ($semUnidade > 0) {
            $this->warn("Atenção: {$semUnidade} desbravador(es) sem unidade_id. Eles não têm vínculo de clube (via unidade) e ficariam invisíveis. Atribua uma unidade manualmente.");
        }
    }

    /** @return \Illuminate\Support\Collection<int,User> */
    private function resolverPlatformAdmins()
    {
        $emails = collect($this->option('platform-admin'))
            ->flatMap(fn ($v) => explode(',', (string) $v))
            ->map(fn ($e) => trim($e))
            ->filter()
            ->unique();

        if ($emails->isEmpty()) {
            return collect();
        }

        $usuarios = User::whereIn('email', $emails->all())->get();

        $faltando = $emails->diff($usuarios->pluck('email'));
        if ($faltando->isNotEmpty()) {
            $this->warn('E-mail(s) de platform admin não encontrado(s) e ignorado(s): '.$faltando->implode(', '));
        }

        return $usuarios;
    }

    private function jaExistePlatformAdmin(): bool
    {
        return User::where('is_platform_admin', true)->exists();
    }
}

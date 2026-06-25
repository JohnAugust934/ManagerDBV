<?php

namespace App\Http\Controllers;

use App\Models\Club;
use App\Models\ClubBackupLog;
use App\Models\Desbravador;
use App\Models\Unidade;
use App\Models\User;
use App\Services\ClubContext;
use App\Services\ClubExportService;
use App\Services\ClubLifecycleService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PlatformController extends Controller
{
    public function index()
    {
        Gate::authorize('platform-admin');

        // Sem ClubScope nas tabelas listadas (Club/Unidade/User não têm scope);
        // Desbravador tem scope, então removemos explicitamente para contar cross-tenant.
        $clubs = Club::query()
            ->orderBy('nome')
            ->get()
            ->map(function (Club $club) {
                return (object) [
                    'model' => $club,
                    'usuarios' => User::where('club_id', $club->id)->count(),
                    'unidades' => Unidade::withoutGlobalScopes()->where('club_id', $club->id)->count(),
                    // club_id direto (Fase 1) — evita whereHas('unidade'), que agora
                    // aplicaria o ClubScope do Unidade e quebraria a contagem cross-tenant.
                    'desbravadores' => Desbravador::withoutGlobalScopes()->where('club_id', $club->id)->count(),
                ];
            });

        // Dados operacionais para o painel de observabilidade
        $operacional = $this->coletarDadosOperacionais();

        return view('platform.index', [
            'clubs' => $clubs,
            'totalClubes' => $clubs->count(),
            'totalUsuarios' => User::whereNotNull('club_id')->count(),
            'operacional' => $operacional,
        ]);
    }

    /**
     * Resumo operacional em JSON para o painel de observabilidade.
     *
     * Contrato (chaves e tipos):
     *  - queueSize: int|null            jobs pendentes na fila (null se a tabela falhar)
     *  - falhasRecentes: int|null       jobs falhos nas últimas 24h
     *  - totalFalhas: int|null          total de jobs falhos
     *  - versao: string                 versão da aplicação (sem shell_exec)
     *  - relatoriosPendentes: int|null  relatórios batch pendentes/processando
     *  - clubesAtivos: int
     *  - clubesInativos: int
     *  - ultimoBackupPorClube: array    lista de { club_id:int, clube:string,
     *                                   filename:string|null, status:string|null,
     *                                   size_bytes:int|null, created_at:string|null }
     *  - queriesLentas: int|null        nº de queries lentas registradas hoje no log;
     *                                   null quando LOG_SLOW_QUERIES está desabilitado
     */
    public function observabilidade(): JsonResponse
    {
        Gate::authorize('platform-admin');

        return response()->json($this->coletarDadosOperacionais());
    }

    public function enterClub(Club $club): RedirectResponse
    {
        Gate::authorize('platform-admin');

        session([ClubContext::SESSION_KEY => $club->id]);

        return redirect()->route('dashboard')
            ->with('success', "Você entrou no modo suporte do clube “{$club->nome}”.");
    }

    public function exitClub(): RedirectResponse
    {
        Gate::authorize('platform-admin');

        session()->forget(ClubContext::SESSION_KEY);

        return redirect()->route('platform.index')
            ->with('success', 'Você saiu do modo suporte.');
    }

    public function exportClub(Club $club, ClubExportService $exporter): StreamedResponse
    {
        Gate::authorize('platform-admin');

        $data = $exporter->export($club);
        $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        return response()->streamDownload(
            fn () => print ($json),
            $exporter->filename($club),
            ['Content-Type' => 'application/json']
        );
    }

    public function toggleActive(Club $club): RedirectResponse
    {
        Gate::authorize('platform-admin');

        $club->update(['is_active' => ! $club->is_active]);

        $status = $club->is_active ? 'reativado' : 'desativado';

        return redirect()->route('platform.index')
            ->with('success', "Clube “{$club->nome}” {$status}. ".
                ($club->is_active ? 'Os usuários já podem acessar.' : 'Nenhum usuário do clube consegue mais entrar.'));
    }

    public function destroy(Club $club, ClubLifecycleService $lifecycle): RedirectResponse
    {
        Gate::authorize('platform-admin');

        // Trava de segurança: só exclui um clube já DESATIVADO (passo deliberado).
        if ($club->is_active) {
            return redirect()->route('platform.index')
                ->with('error', 'Desative o clube antes de excluí-lo definitivamente.');
        }

        // Se estiver dando suporte a ESTE clube, encerra a impersonação antes.
        if (ClubContext::isImpersonating() && ClubContext::currentClubId() === $club->id) {
            session()->forget(ClubContext::SESSION_KEY);
        }

        $nome = $club->nome;
        $lifecycle->delete($club);

        return redirect()->route('platform.index')
            ->with('success', "Clube “{$nome}” e todos os seus dados foram removidos definitivamente.");
    }

    public function createClub()
    {
        Gate::authorize('platform-admin');

        return view('platform.create-club');
    }

    public function storeClub(Request $request): RedirectResponse
    {
        Gate::authorize('platform-admin');

        $validated = $request->validate([
            'nome' => 'required|string|max:255',
            'cidade' => 'required|string|max:255',
            'associacao' => 'nullable|string|max:255',
            'master_name' => 'required|string|max:255',
            'master_email' => 'required|email|unique:users,email',
            'master_password' => 'required|string|min:8',
        ]);

        $club = Club::create([
            'nome' => $validated['nome'],
            'cidade' => $validated['cidade'],
            'associacao' => $validated['associacao'] ?? null,
        ]);

        User::create([
            'name' => $validated['master_name'],
            'email' => $validated['master_email'],
            'password' => Hash::make($validated['master_password']),
            'role' => 'master',
            'is_master' => true,
            'is_platform_admin' => false,
            'club_id' => $club->id,
            'email_verified_at' => now(),
        ]);

        return redirect()->route('platform.index')
            ->with('success', 'Clube “'.$club->nome.'” criado com seu master inicial.');
    }

    private function coletarDadosOperacionais(): array
    {
        // Jobs na fila
        try {
            $queueSize = DB::table('jobs')->count();
        } catch (\Exception) {
            $queueSize = null;
        }

        // Jobs falhos nas últimas 24h
        try {
            $falhasRecentes = DB::table('failed_jobs')
                ->where('failed_at', '>=', now()->subDay()->toDateTimeString())
                ->count();
            $totalFalhas = DB::table('failed_jobs')->count();
        } catch (\Exception) {
            $falhasRecentes = null;
            $totalFalhas = null;
        }

        // Versão da aplicação (commit mais recente)
        $versao = $this->resolverVersao();

        // Relatórios batch pendentes/processando
        try {
            $relatoriosPendentes = \App\Models\RelatorioGerado::withoutGlobalScopes()
                ->whereIn('status', ['pendente', 'processando'])
                ->count();
        } catch (\Exception) {
            $relatoriosPendentes = null;
        }

        // Clubes ativos vs inativos
        $clubesAtivos = Club::where('is_active', true)->count();
        $clubesInativos = Club::where('is_active', false)->count();

        // Último backup por clube
        $ultimoBackupPorClube = $this->ultimoBackupPorClube();

        // Queries lentas registradas hoje (só quando o log está habilitado)
        $queriesLentas = $this->contarQueriesLentasHoje();

        return compact(
            'queueSize',
            'falhasRecentes',
            'totalFalhas',
            'versao',
            'relatoriosPendentes',
            'clubesAtivos',
            'clubesInativos',
            'ultimoBackupPorClube',
            'queriesLentas',
        );
    }

    /**
     * Último backup de cada clube (a partir de club_backup_logs). Retorna uma
     * lista com um item por clube que possua ao menos um registro de backup.
     *
     * @return array<int, array<string, mixed>>
     */
    private function ultimoBackupPorClube(): array
    {
        try {
            // MAX(id) por club_id identifica o registro mais recente (id auto-incremento).
            $ultimos = ClubBackupLog::query()
                ->whereIn('id', function ($q) {
                    $q->selectRaw('MAX(id)')
                        ->from('club_backup_logs')
                        ->groupBy('club_id');
                })
                ->with('club:id,nome')
                ->get();

            return $ultimos->map(fn (ClubBackupLog $log) => [
                'club_id' => $log->club_id,
                'clube' => $log->club?->nome,
                'filename' => $log->filename,
                'status' => $log->status,
                'size_bytes' => $log->size_bytes,
                'created_at' => $log->created_at?->toIso8601String(),
            ])->values()->all();
        } catch (\Exception) {
            return [];
        }
    }

    /**
     * Conta as queries lentas registradas hoje nos logs da aplicação. O slow
     * query log é opt-in (LOG_SLOW_QUERIES); quando desabilitado retorna null
     * para sinalizar "não monitorado" em vez de "zero".
     */
    private function contarQueriesLentasHoje(): ?int
    {
        if (! env('LOG_SLOW_QUERIES', false)) {
            return null;
        }

        try {
            $hoje = now()->format('Y-m-d');
            $total = 0;

            foreach (File::glob(storage_path('logs').'/*.log') as $arquivo) {
                foreach (preg_split('/\R/', (string) @file_get_contents($arquivo)) as $linha) {
                    if (str_contains($linha, '['.$hoje) && str_contains($linha, 'Slow query')) {
                        $total++;
                    }
                }
            }

            return $total;
        } catch (\Exception) {
            return null;
        }
    }

    /**
     * Resolve a versão da aplicação (hash curto do commit) sem depender de
     * shell_exec/git — vários hostings (ex.: Hostinger) desabilitam shell_exec.
     *
     * Ordem de resolução:
     *  1. leitura direta dos arquivos .git/ via PHP puro (fonte da verdade,
     *     atualiza sozinho a cada deploy via git);
     *  2. shell_exec (git), se o hosting permitir;
     *  3. arquivo VERSION na raiz do projeto (gerado no deploy);
     *  4. APP_VERSION explícito no .env (override manual).
     */
    private function resolverVersao(): string
    {
        // 1. Lê o commit direto do diretório .git sem precisar do binário git.
        $versao = $this->lerCommitDoGit();
        if ($versao !== '') {
            return $versao;
        }

        // 2. shell_exec, se o hosting permitir (a maioria dos planos Hostinger não).
        try {
            if (function_exists('shell_exec')) {
                $versao = trim((string) shell_exec('git rev-parse --short HEAD 2>/dev/null'));
                if ($versao !== '') {
                    return $versao;
                }
            }
        } catch (\Throwable) {
            // ignora e tenta próximas estratégias
        }

        // 3. Arquivo VERSION na raiz (pode ser gerado no passo de deploy).
        try {
            $versionFile = base_path('VERSION');
            if (is_file($versionFile)) {
                $versao = trim((string) file_get_contents($versionFile));
                if ($versao !== '') {
                    return $versao;
                }
            }
        } catch (\Throwable) {
            // ignora
        }

        // 4. Override manual via .env (APP_VERSION) — só se diferente do default.
        $appVersion = trim((string) env('APP_VERSION', ''));
        if ($appVersion !== '') {
            return $appVersion;
        }

        return 'desconhecida';
    }

    /**
     * Lê o hash do commit atual lendo os arquivos do diretório .git
     * diretamente (HEAD -> ref -> sha, ou packed-refs). Retorna o hash
     * curto (7 caracteres) ou string vazia se não conseguir resolver.
     */
    private function lerCommitDoGit(): string
    {
        try {
            $gitDir = base_path('.git');
            if (! is_dir($gitDir)) {
                return '';
            }

            $head = trim((string) @file_get_contents($gitDir.'/HEAD'));
            if ($head === '') {
                return '';
            }

            // HEAD detached: já é o próprio hash.
            if (! str_starts_with($head, 'ref:')) {
                return substr($head, 0, 7);
            }

            $ref = trim(substr($head, 4)); // ex.: refs/heads/multi-tenant
            $refFile = $gitDir.'/'.$ref;

            if (is_file($refFile)) {
                $sha = trim((string) @file_get_contents($refFile));
                if ($sha !== '') {
                    return substr($sha, 0, 7);
                }
            }

            // Ref empacotada em packed-refs.
            $packed = @file_get_contents($gitDir.'/packed-refs');
            if ($packed !== false) {
                foreach (preg_split('/\R/', $packed) as $linha) {
                    $linha = trim($linha);
                    if ($linha === '' || $linha[0] === '#' || $linha[0] === '^') {
                        continue;
                    }
                    [$sha, $nome] = array_pad(explode(' ', $linha, 2), 2, '');
                    if (trim($nome) === $ref && $sha !== '') {
                        return substr($sha, 0, 7);
                    }
                }
            }
        } catch (\Throwable) {
            // ignora
        }

        return '';
    }
}

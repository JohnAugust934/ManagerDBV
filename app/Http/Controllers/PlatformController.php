<?php

namespace App\Http\Controllers;

use App\Models\Club;
use App\Models\Desbravador;
use App\Models\Unidade;
use App\Models\User;
use App\Services\ClubContext;
use App\Services\ClubExportService;
use App\Services\ClubLifecycleService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
        try {
            $versao = trim((string) shell_exec('git rev-parse --short HEAD 2>/dev/null'));
            $versao = $versao ?: 'desconhecida';
        } catch (\Exception) {
            $versao = 'desconhecida';
        }

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

        return compact(
            'queueSize',
            'falhasRecentes',
            'totalFalhas',
            'versao',
            'relatoriosPendentes',
            'clubesAtivos',
            'clubesInativos',
        );
    }
}

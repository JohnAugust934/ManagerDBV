<?php

namespace App\Http\Controllers;

use App\Models\Club;
use App\Models\ClubBackupLog;
use App\Services\ClubBackupService;
use App\Services\ClubContext;
use App\Services\ClubRestoreService;
use App\Services\TelegramNotifier;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;

/**
 * Gerencia backups isolados por clube.
 *
 * Acesso permitido a:
 *   - Master do próprio clube (role=master, club_id definido)
 *   - Platform admin impersonando um clube (ClubContext::isImpersonating())
 *
 * Nenhuma operação cruza fronteiras de clube: o clube ativo é sempre o do
 * contexto corrente e a validação de posse é feita antes de qualquer ação.
 */
class ClubBackupController extends Controller
{
    public function __construct(
        private readonly ClubBackupService $backupService,
        private readonly ClubRestoreService $restoreService
    ) {}

    public function index(Request $request)
    {
        $club = $this->resolveClub();

        $allBackups = $this->backupService->listBackups($club);

        $perPage = 10;
        $page = LengthAwarePaginator::resolveCurrentPage();
        $total = count($allBackups);
        $itens = array_slice($allBackups, ($page - 1) * $perPage, $perPage);

        $backups = new LengthAwarePaginator($itens, $total, $perPage, $page, [
            'path' => $request->url(),
            'query' => $request->query(),
        ]);

        return view('club.backups.index', compact('backups', 'club'));
    }

    public function store()
    {
        $club = $this->resolveClub();
        set_time_limit(0);
        ini_set('memory_limit', '-1');

        try {
            $result = $this->backupService->backup(
                $club,
                origin: 'manual',
                createdBy: auth()->id()
            );

            $this->notify("Backup manual do clube {$club->nome}", [
                'Responsável' => auth()->user()?->name,
                'Clube' => $club->nome,
                'Arquivo' => $result['filename'],
                'Tamanho' => round($result['size'] / 1048576, 2).' MB',
                'Discos' => implode(', ', $result['disks']),
            ], 'success');

            return back()->with('success', "Backup do clube {$club->nome} gerado com sucesso!");

        } catch (\Throwable $e) {
            Log::error("ClubBackupController: falha no backup do clube {$club->id}", ['error' => $e->getMessage()]);
            $this->notify("Falha no backup do clube {$club->nome}", [
                'Clube' => $club->nome,
                'Erro' => $e->getMessage(),
            ], 'error');

            return back()->with('error', 'Falha ao gerar o backup. Verifique os logs do sistema.');
        }
    }

    public function restore(Request $request)
    {
        $club = $this->resolveClub();
        set_time_limit(0);
        ini_set('memory_limit', '-1');

        [$disk, $path] = $this->normalizeSelection(
            (string) $request->input('disk', ''),
            (string) $request->input('path', ''),
            $club
        );

        try {
            $report = $this->restoreService->restore($club, $disk, $path);

            $responsavel = auth()->user()?->name;
            $avisos = $report['warnings'] ?? [];
            $counts = $report['counts'] ?? [];

            $this->notify("Restauração de backup do clube {$club->nome}", [
                'Responsável' => $responsavel,
                'Clube' => $club->nome,
                'Arquivo' => basename($path),
                'Registros' => collect($counts)->sum().' linhas restauradas',
                'Avisos' => count($avisos) > 0 ? implode('; ', $avisos) : 'nenhum',
            ], 'warning');

            $msg = "Restauração concluída para o clube {$club->nome}. ".
                collect($counts)->sum().' registros restaurados.';

            if (! empty($avisos)) {
                $msg .= ' Avisos: '.implode(' | ', $avisos);
            }

            // Se for master, desloga pois os IDs de usuário foram remapeados
            if (! auth()->user()?->is_platform_admin) {
                auth()->logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect('/login')->with('success', $msg.' Faça login novamente com os dados do backup.');
            }

            return back()->with('success', $msg);

        } catch (\Throwable $e) {
            Log::error("ClubBackupController: falha no restore do clube {$club->id}", ['error' => $e->getMessage()]);
            $this->notify("Falha na restauração do clube {$club->nome}", [
                'Clube' => $club->nome,
                'Arquivo' => basename($path),
                'Erro' => $e->getMessage(),
            ], 'error');

            return back()->with('error', 'Falha na restauração: '.$e->getMessage());
        }
    }

    public function download(Request $request)
    {
        $club = $this->resolveClub();
        set_time_limit(0);
        ini_set('memory_limit', '-1');

        [$disk, $path] = $this->normalizeSelection(
            (string) $request->query('disk', ''),
            (string) $request->query('path', ''),
            $club
        );

        $this->assertExists($disk, $path);

        return \Illuminate\Support\Facades\Storage::disk($disk)->download(
            $path,
            basename($path),
            ['Content-Type' => 'application/zip']
        );
    }

    public function destroy(Request $request)
    {
        $club = $this->resolveClub();

        [$disk, $path] = $this->normalizeSelection(
            (string) $request->input('disk', ''),
            (string) $request->input('path', ''),
            $club
        );

        if (\Illuminate\Support\Facades\Storage::disk($disk)->exists($path)) {
            \Illuminate\Support\Facades\Storage::disk($disk)->delete($path);

            // Exclusao e definitiva (sem soft delete). Registra quem/quando para
            // auditoria — o log sobrevive mesmo apos o arquivo sumir.
            ClubBackupLog::create([
                'club_id' => $club->id,
                'disk' => $disk,
                'path' => $path,
                'filename' => basename($path),
                'status' => 'excluido',
                'created_by' => auth()->id(),
                'origin' => 'manual',
            ]);

            $this->notify("Backup do clube {$club->nome} excluído", [
                'Responsável' => auth()->user()?->name,
                'Clube' => $club->nome,
                'Arquivo' => basename($path),
            ], 'warning');

            return back()->with('success', 'Backup excluído permanentemente.');
        }

        return back()->with('error', 'Arquivo não encontrado.');
    }

    /** Resolve o clube ativo do contexto e valida a autorização. */
    private function resolveClub(): Club
    {
        $user = auth()->user();

        if (! $user) {
            abort(403);
        }

        // Platform admin impersonando um clube
        if ($user->is_platform_admin) {
            if (! ClubContext::isImpersonating()) {
                abort(403, 'Selecione um clube (modo suporte) para acessar os backups de clube.');
            }
            $club = ClubContext::currentClub();
            if (! $club) {
                abort(404, 'Clube não encontrado.');
            }

            return $club;
        }

        // Master do clube
        Gate::authorize('master');
        $club = Club::find($user->club_id);
        if (! $club) {
            abort(404, 'Clube não encontrado.');
        }

        return $club;
    }

    /** Normaliza e valida os parâmetros disk/path, garantindo que pertencem ao clube. */
    private function normalizeSelection(string $disk, string $path, Club $club): array
    {
        $this->backupService->assertBelongsToClub($club, $disk, $path);

        return [$disk, str_replace('\\', '/', ltrim($path, '/\\'))];
    }

    private function assertExists(string $disk, string $path): void
    {
        if (! \Illuminate\Support\Facades\Storage::disk($disk)->exists($path)) {
            abort(404, 'Arquivo de backup não encontrado.');
        }
    }

    private function notify(string $title, array $details, string $status): void
    {
        try {
            app(TelegramNotifier::class)->notifyAdministrativeAction($title, $details, $status);
        } catch (\Throwable) {
        }
    }
}

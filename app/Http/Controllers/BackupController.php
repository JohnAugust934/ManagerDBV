<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class BackupController extends Controller
{
    public function index()
    {
        Gate::authorize('master');

        $disks = ['local', 'r2'];
        $backups = [];
        $errosDiscos = [];

        $backupName = config('backup.backup.name', 'Laravel');

        foreach ($disks as $disk) {
            try {
                $files = Storage::disk($disk)->files($backupName);

                if (empty($files)) {
                    $files = Storage::disk($disk)->allFiles($backupName);
                }

                foreach ($files as $file) {
                    if (str_ends_with(strtolower($file), '.zip')) {
                        $backups[] = [
                            'disk' => $disk,
                            'path' => $file,
                            'name' => basename($file),
                            'size' => round(Storage::disk($disk)->size($file) / 1048576, 2),
                            'date' => Carbon::createFromTimestamp(Storage::disk($disk)->lastModified($file)),
                        ];
                    }
                }
            } catch (\Exception $e) {
                Log::error("Erro ao ler backups do disco {$disk}: ".$e->getMessage());
                $errosDiscos[] = strtoupper($disk).' falhou';
            }
        }

        usort($backups, fn ($a, $b) => $b['date'] <=> $a['date']);

        if (! empty($errosDiscos)) {
            session()->flash('warning', 'Aviso: Falha de leitura em alguns discos ('.implode(', ', $errosDiscos).')');
        }

        return view('admin.backups.index', compact('backups'));
    }

    public function store()
    {
        Gate::authorize('master');

        set_time_limit(0);
        ini_set('memory_limit', '-1');

        try {
            $exitCode = Artisan::call('backup:run', [
                '--disable-notifications' => true,
            ]);

            $output = Artisan::output();

            if ($exitCode === 0) {
                return back()->with('success', 'Backup gerado localmente e sincronizado com a Nuvem!');
            } else {
                Log::error('Erro do Backup Web: '.$output);

                // Trata a trava específica do Windows + PostgreSQL
                if (str_contains($output, 'could not generate restrict key')) {
                    return back()->with('warning', '⚠️ Bloqueio Local (Windows): O servidor web não tem permissão de criptografia para rodar o pg_dump no Windows. Este erro NÃO ocorrerá na produção (Hostinger/Linux). Localmente, use o comando "php artisan backup:run" no terminal.');
                }

                if (str_contains($output, 'The dump process failed')) {
                    return back()->with('error', 'Falha no banco de dados. O executável de backup não foi encontrado no caminho especificado na configuração.');
                }

                return back()->with('error', 'Falha no processo de backup. Verifique os logs do sistema para mais detalhes.');
            }
        } catch (\Exception $e) {
            Log::error('Exceção Crítica no Backup: '.$e->getMessage());

            return back()->with('error', 'Erro interno no servidor ao tentar gerar o backup.');
        }
    }

    public function download(Request $request)
    {
        Gate::authorize('master');

        $disk = $request->query('disk');
        $path = $request->query('path');

        if (Storage::disk($disk)->exists($path)) {
            return Storage::disk($disk)->download($path);
        }

        return back()->with('error', 'Arquivo não encontrado no disco especificado.');
    }

    public function destroy(Request $request)
    {
        Gate::authorize('master');

        $disk = $request->input('disk');
        $path = $request->input('path');

        if (Storage::disk($disk)->exists($path)) {
            Storage::disk($disk)->delete($path);

            return back()->with('success', 'Backup excluído com sucesso do disco '.strtoupper($disk).'!');
        }

        return back()->with('error', 'Arquivo não encontrado.');
    }
}

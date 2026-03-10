<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
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
            $exitCode = Artisan::call('backup:run', ['--disable-notifications' => true]);
            $output = Artisan::output();

            if ($exitCode === 0) {
                return back()->with('success', 'Backup gerado localmente e sincronizado com a Nuvem!');
            } else {
                Log::error('Erro do Backup Web: '.$output);
                if (str_contains($output, 'could not generate restrict key')) {
                    return back()->with('warning', '⚠️ Bloqueio Local (Windows): O servidor web não tem permissão para rodar o pg_dump. Na produção (Linux) funcionará normalmente. Localmente, use o terminal.');
                }
                if (str_contains($output, 'The dump process failed')) {
                    return back()->with('error', 'Falha no banco de dados. O executável de backup não foi encontrado.');
                }

                return back()->with('error', 'Falha no processo de backup. Verifique os logs do sistema.');
            }
        } catch (\Exception $e) {
            Log::error('Exceção Crítica no Backup: '.$e->getMessage());

            return back()->with('error', 'Erro interno no servidor ao tentar gerar o backup.');
        }
    }

    public function import(Request $request)
    {
        Gate::authorize('master');

        if (empty($_FILES) && $request->server('CONTENT_LENGTH') > 0) {
            return back()->with('error', 'O arquivo é maior que o limite de upload configurado no seu servidor local (upload_max_filesize no php.ini).');
        }

        $request->validate([
            'backup_file' => 'required|file',
        ], [
            'backup_file.required' => 'Nenhum arquivo foi selecionado.',
            'backup_file.file' => 'O arquivo enviado é inválido ou está corrompido.',
        ]);

        try {
            $file = $request->file('backup_file');

            if (strtolower($file->getClientOriginalExtension()) !== 'zip') {
                return back()->with('error', 'Formato não aceito. O arquivo precisa obrigatoriamente ser um .zip gerado pelo sistema.');
            }

            $backupName = config('backup.backup.name', 'Laravel');

            $file->storeAs($backupName, $file->getClientOriginalName(), 'local');

            return back()->with('success', 'Arquivo importado com sucesso! Ele já está disponível na lista abaixo para ser restaurado.');
        } catch (\Exception $e) {
            Log::error('Erro ao importar backup: '.$e->getMessage());

            return back()->with('error', 'Falha ao salvar o arquivo enviado: '.$e->getMessage());
        }
    }

    public function restore(Request $request)
    {
        Gate::authorize('master');

        set_time_limit(0);
        ini_set('memory_limit', '-1');

        $disk = $request->input('disk');
        $path = $request->input('path');

        try {
            $tempZipPath = storage_path('app/temp_restore.zip');
            if ($disk === 'local') {
                File::copy(Storage::disk('local')->path($path), $tempZipPath);
            } else {
                file_put_contents($tempZipPath, Storage::disk($disk)->get($path));
            }

            $extractPath = storage_path('app/temp_restore_dir');
            File::deleteDirectory($extractPath);
            File::makeDirectory($extractPath, 0755, true);

            $zip = new \ZipArchive;
            if ($zip->open($tempZipPath) === true) {
                $zip->extractTo($extractPath);
                $zip->close();
            } else {
                throw new \Exception('O arquivo selecionado está corrompido ou não é um ZIP válido.');
            }

            $allExtractedFiles = File::allFiles($extractPath);
            $dbRestored = false;
            $filesRestored = 0;
            $sqlFileToRestore = null;

            foreach ($allExtractedFiles as $file) {
                $filePath = str_replace('\\', '/', $file->getPathname());

                if (str_ends_with($filePath, '.sql') && str_contains($filePath, 'db-dumps')) {
                    $sqlFileToRestore = $file->getPathname();
                }

                if (str_contains($filePath, '/app/public/')) {
                    $relativePath = explode('/app/public/', $filePath)[1];
                    Storage::disk('public')->put($relativePath, File::get($filePath));
                    $filesRestored++;
                }
            }

            if ($sqlFileToRestore) {
                $connection = config('database.default');
                $comando = '';

                if ($connection === 'pgsql') {
                    $url = config('database.connections.pgsql.url');
                    if (empty($url)) {
                        $host = config('database.connections.pgsql.host');
                        $port = config('database.connections.pgsql.port');
                        $db = config('database.connections.pgsql.database');
                        $user = config('database.connections.pgsql.username');
                        $pass = config('database.connections.pgsql.password');
                        $url = "postgresql://{$user}:{$pass}@{$host}:{$port}/{$db}?sslmode=require";
                    }

                    $binPath = config('database.connections.pgsql.dump.dump_binary_path', '');
                    $psqlCmd = $binPath ? rtrim($binPath, '\\/').DIRECTORY_SEPARATOR.'psql' : 'psql';

                    $comando = '"'.$psqlCmd.'" "'.$url.'" -f "'.$sqlFileToRestore.'" 2>&1';

                } elseif ($connection === 'mysql' || $connection === 'mariadb') {
                    $host = config('database.connections.mysql.host');
                    $db = config('database.connections.mysql.database');
                    $user = config('database.connections.mysql.username');
                    $pass = config('database.connections.mysql.password');

                    $binPath = config('database.connections.mysql.dump.dump_binary_path', '');
                    $mysqlCmd = $binPath ? rtrim($binPath, '\\/').DIRECTORY_SEPARATOR.'mysql' : 'mysql';

                    $comando = '"'.$mysqlCmd.'" -h '.escapeshellarg($host).' -u '.escapeshellarg($user).(! empty($pass) ? ' -p'.escapeshellarg($pass) : '').' '.escapeshellarg($db).' < "'.$sqlFileToRestore.'" 2>&1';
                }

                Artisan::call('db:wipe', ['--force' => true]);

                $output = [];
                $returnVar = 0;
                exec($comando, $output, $returnVar);

                if ($returnVar !== 0) {
                    throw new \Exception('Falha no comando nativo de restauração: '.implode(' ', $output));
                }

                Artisan::call('migrate', ['--force' => true]);

                $dbRestored = true;
            }

            File::deleteDirectory($extractPath);
            @unlink($tempZipPath);

            $statusDB = $dbRestored ? 'Banco de Dados restaurado' : 'Nenhum banco encontrado no backup';

            auth()->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect('/login')->with('success', "Restauração Finalizada! [{$statusDB}] e [{$filesRestored} imagens restauradas]. Faça login com os dados da época do backup.");

        } catch (\Exception $e) {
            Log::error('Falha Crítica na Restauração: '.$e->getMessage());

            File::deleteDirectory(storage_path('app/temp_restore_dir'));
            @unlink(storage_path('app/temp_restore.zip'));

            Artisan::call('migrate', ['--force' => true]);

            return back()->with('error', 'Erro na restauração: '.$e->getMessage());
        }
    }

    // ==========================================
    // DOWNLOAD BLINDADO CONTRA TELA BRANCA
    // ==========================================
    public function download(Request $request)
    {
        Gate::authorize('master');

        set_time_limit(0);
        ini_set('memory_limit', '-1');

        $disk = $request->query('disk');
        $path = $request->query('path');

        if (! Storage::disk($disk)->exists($path)) {
            return back()->with('error', 'Arquivo não encontrado.');
        }

        // Limpa o buffer apenas se NÃO estiver rodando testes automatizados
        // Isso evita a tela branca em produção e o aviso "risky" no PHPUnit
        if (ob_get_level() > 0 && ! app()->runningUnitTests()) {
            ob_end_clean();
        }

        $nomeArquivo = basename($path);

        return Storage::disk($disk)->download($path, $nomeArquivo, [
            'Content-Type' => 'application/zip',
            'Content-Disposition' => 'attachment; filename="'.$nomeArquivo.'"',
        ]);
    }

    public function destroy(Request $request)
    {
        Gate::authorize('master');
        $disk = $request->input('disk');
        $path = $request->input('path');

        if (Storage::disk($disk)->exists($path)) {
            Storage::disk($disk)->delete($path);

            return back()->with('success', 'Backup excluído permanentemente.');
        }

        return back()->with('error', 'Arquivo não encontrado.');
    }
}

<?php

namespace App\Http\Controllers;

use App\Services\TelegramNotifier;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\Process\Process;

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
            [$exitCode, $output] = $this->runManualBackup();

            if ($exitCode === 0) {
                $this->notifyAdminAction('Backup manual executado', [
                    'Responsavel' => auth()->user()?->name,
                    'Origem' => 'Tela de backups',
                ], 'success');

                return back()->with('success', 'Backup gerado localmente e sincronizado com a Nuvem!');
            } else {
                Log::error('Erro do Backup Web: '.$output);
                $this->notifyAdminAction('Falha no backup manual', [
                    'Responsavel' => auth()->user()?->name,
                    'Origem' => 'Tela de backups',
                    'Erro' => $output,
                ], 'error');
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
            $this->notifyAdminAction('Erro interno ao gerar backup manual', [
                'Responsavel' => auth()->user()?->name,
                'Origem' => 'Tela de backups',
                'Erro' => $e->getMessage(),
            ], 'error');

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
            $conteudo = $file->get();

            if (strtolower($file->getClientOriginalExtension()) !== 'zip') {
                return back()->with('error', 'Formato não aceito. O arquivo precisa obrigatoriamente ser um .zip gerado pelo sistema.');
            }

            $temporaryValidationFile = storage_path('app/tmp-import-'.Str::uuid().'.zip');
            File::ensureDirectoryExists(dirname($temporaryValidationFile));
            file_put_contents($temporaryValidationFile, $conteudo);

            $zip = new \ZipArchive;
            $zipCheck = $zip->open($temporaryValidationFile);

            if ($zipCheck !== true) {
                @unlink($temporaryValidationFile);
                return back()->with('error', 'O arquivo enviado não é um ZIP válido ou está corrompido.');
            }

            $zip->close();
            @unlink($temporaryValidationFile);

            $backupName = trim((string) config('backup.backup.name', 'Laravel'), '/\\');
            if ($backupName === '') {
                $backupName = 'Laravel';
            }
            $safeFileName = preg_replace('/[^A-Za-z0-9._-]/', '-', pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME));
            $extension = $file->getClientOriginalExtension();
            $finalFileName = trim($safeFileName ?: 'backup-importado', '.-').'-'.now()->format('Ymd-His').'.'.$extension;
            $targetPath = $backupName.'/'.$finalFileName;

            if (! Storage::disk('local')->put($targetPath, $conteudo)) {
                throw new \RuntimeException('Não foi possível salvar o backup importado no disco local.');
            }

            $this->notifyAdminAction('Backup importado manualmente', [
                'Responsavel' => auth()->user()?->name,
                'Arquivo' => $finalFileName,
            ], 'success');

            return back()->with('success', 'Arquivo importado com sucesso! Ele já está disponível na lista abaixo para ser restaurado.');
        } catch (\Exception $e) {
            Log::error('Erro ao importar backup: '.$e->getMessage());
            $this->notifyAdminAction('Falha ao importar backup', [
                'Responsavel' => auth()->user()?->name,
                'Erro' => $e->getMessage(),
            ], 'error');

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
        $allowedDisks = ['local', 'r2'];
        $tempZipPath = storage_path('app/temp_restore.zip');
        $extractPath = storage_path('app/temp_restore_dir');
        $emergencySnapshotPath = storage_path('app/pre_restore_snapshot.sql');
        $maintenanceEnabled = false;
        $databaseWasWiped = false;
        $emergencySnapshotCreated = false;

        try {
            if (! in_array($disk, $allowedDisks, true)) {
                throw new \RuntimeException('Disco de backup inválido.');
            }

            if (! $path || ! Storage::disk($disk)->exists($path)) {
                throw new \RuntimeException('Arquivo de backup não encontrado.');
            }

            Artisan::call('down');
            $maintenanceEnabled = true;

            File::deleteDirectory($extractPath);
            @unlink($tempZipPath);
            @unlink($emergencySnapshotPath);

            // 1. Traz o arquivo para a área de extração local
            if ($disk === 'local') {
                File::copy(Storage::disk('local')->path($path), $tempZipPath);
            } else {
                file_put_contents($tempZipPath, Storage::disk($disk)->get($path));
            }

            // 2. Valida e extrai o arquivo sem permitir path traversal
            $this->extractBackupArchiveSafely($tempZipPath, $extractPath);

            // 3. Procura os arquivos essenciais antes de tocar no banco
            $allExtractedFiles = File::allFiles($extractPath);
            if (empty($allExtractedFiles)) {
                throw new \RuntimeException('O backup está vazio ou não pôde ser lido.');
            }

            $filesRestored = 0;
            $sqlFileToRestore = null;
            $sqliteFileToRestore = null;
            $publicFiles = [];

            foreach ($allExtractedFiles as $file) {
                $filePath = str_replace('\\', '/', $file->getPathname());

                if (str_ends_with($filePath, '.sql') && str_contains($filePath, 'db-dumps')) {
                    $sqlFileToRestore = $file->getPathname();
                }

                if (str_ends_with($filePath, '.sqlite') && str_contains($filePath, '/database/')) {
                    $sqliteFileToRestore = $file->getPathname();
                }

                if (str_contains($filePath, '/app/public/')) {
                    $publicFiles[] = $filePath;
                }
            }

            $databaseFileToRestore = $sqlFileToRestore ?? $sqliteFileToRestore;

            if (! $databaseFileToRestore && empty($publicFiles)) {
                throw new \RuntimeException('O arquivo enviado não contém dados restauráveis do sistema.');
            }

            // 4. Cria snapshot de emergência antes de qualquer mudança destrutiva
            if ($databaseFileToRestore) {
                $this->createEmergencyDatabaseSnapshot($emergencySnapshotPath);
                $emergencySnapshotCreated = true;
                Artisan::call('db:wipe', ['--force' => true]);
                $databaseWasWiped = true;
                $this->restoreDatabaseFromBackup($databaseFileToRestore);

                if (! $sqliteFileToRestore) {
                    Artisan::call('migrate', ['--force' => true]);
                }
            }

            // 5. Só restaura arquivos públicos depois que o banco estiver consistente
            foreach ($publicFiles as $filePath) {
                $relativePath = explode('/app/public/', $filePath, 2)[1];
                Storage::disk('public')->put($relativePath, File::get($filePath));
                $filesRestored++;
            }

            // 6. Limpeza de temp files
            File::deleteDirectory($extractPath);
            @unlink($tempZipPath);
            @unlink($emergencySnapshotPath);

            Artisan::call('up');
            $maintenanceEnabled = false;

            $statusDB = $databaseFileToRestore ? 'Banco de Dados restaurado' : 'Nenhum banco encontrado no backup';
            $responsavel = auth()->user()?->name;

            auth()->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            $this->notifyAdminAction('Restauracao de backup concluida', [
                'Responsavel' => $responsavel,
                'Disco' => $disk,
                'Arquivo' => basename($path),
                'Banco' => $statusDB,
                'Arquivos restaurados' => $filesRestored,
            ], 'warning');

            return redirect('/login')->with('success', "Restauração Finalizada! [{$statusDB}] e [{$filesRestored} imagens restauradas]. Faça login com os dados da época do backup.");

        } catch (\Exception $e) {
            if ($databaseWasWiped && $emergencySnapshotCreated) {
                try {
                    $this->restoreDatabaseFromEmergencySnapshot($emergencySnapshotPath);
                    Artisan::call('migrate', ['--force' => true]);
                    Log::warning('Restauração do backup falhou, mas o banco anterior foi recuperado a partir do snapshot de emergência.');
                } catch (\Exception $rollbackException) {
                    Log::critical('Falha ao recuperar snapshot de emergência após erro na restauração: '.$rollbackException->getMessage());
                }
            }

            Log::error('Falha Crítica na Restauração: '.$e->getMessage());

            File::deleteDirectory($extractPath);
            @unlink($tempZipPath);
            @unlink($emergencySnapshotPath);

            if ($maintenanceEnabled) {
                Artisan::call('up');
            }

            $this->notifyAdminAction('Falha na restauracao de backup', [
                'Responsavel' => auth()->user()?->name,
                'Disco' => $disk,
                'Arquivo' => basename((string) $path),
                'Erro' => $e->getMessage(),
            ], 'error');

            return back()->with('error', 'Erro na restauração: '.$e->getMessage());
        }
    }

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
            $this->notifyAdminAction('Backup excluido manualmente', [
                'Responsavel' => auth()->user()?->name,
                'Disco' => $disk,
                'Arquivo' => basename($path),
            ], 'warning');

            return back()->with('success', 'Backup excluído permanentemente.');
        }

        return back()->with('error', 'Arquivo não encontrado.');
    }

    private function extractBackupArchiveSafely(string $zipPath, string $extractPath): void
    {
        File::makeDirectory($extractPath, 0755, true, true);

        $zip = new \ZipArchive;
        $result = $zip->open($zipPath);

        if ($result !== true) {
            throw new \RuntimeException('O arquivo selecionado está corrompido ou não é um ZIP válido.');
        }

        try {
            if ($zip->numFiles === 0) {
                throw new \RuntimeException('O arquivo ZIP está vazio.');
            }

            for ($index = 0; $index < $zip->numFiles; $index++) {
                $entryName = str_replace('\\', '/', $zip->getNameIndex($index));
                $normalizedEntry = ltrim($entryName, '/');

                if ($normalizedEntry === '' || str_contains($normalizedEntry, '../') || preg_match('/^[A-Za-z]:\//', $normalizedEntry)) {
                    throw new \RuntimeException('O backup contém caminhos inválidos e foi bloqueado por segurança.');
                }

                $destinationPath = $extractPath.DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $normalizedEntry);

                if (str_ends_with($entryName, '/')) {
                    File::makeDirectory($destinationPath, 0755, true, true);

                    continue;
                }

                File::makeDirectory(dirname($destinationPath), 0755, true, true);

                $stream = $zip->getStream($entryName);
                if (! $stream) {
                    throw new \RuntimeException("Não foi possível ler o item '{$entryName}' do backup.");
                }

                $target = fopen($destinationPath, 'wb');
                if ($target === false) {
                    fclose($stream);
                    throw new \RuntimeException("Não foi possível preparar o destino de extração para '{$entryName}'.");
                }

                stream_copy_to_stream($stream, $target);
                fclose($stream);
                fclose($target);
            }
        } finally {
            $zip->close();
        }
    }

    private function runManualBackup(): array
    {
        if (app()->runningUnitTests()) {
            $exitCode = Artisan::call('backup:run', ['--disable-notifications' => true]);

            return [$exitCode, Artisan::output()];
        }

        $process = new Process([
            PHP_BINARY,
            'artisan',
            'backup:run',
            '--disable-notifications',
        ], base_path(), null, null, null);

        $process->setTimeout(null);
        $process->run();

        return [
            $process->getExitCode() ?? 1,
            trim($process->getOutput().PHP_EOL.$process->getErrorOutput()),
        ];
    }

    private function createEmergencyDatabaseSnapshot(string $snapshotPath): void
    {
        $connection = config('database.default');

        if ($connection === 'pgsql') {
            $this->dumpPostgresDatabase($snapshotPath);

            return;
        }

        if ($connection === 'mysql' || $connection === 'mariadb') {
            $this->dumpMysqlDatabase($snapshotPath);

            return;
        }

        if ($connection === 'sqlite') {
            $databasePath = config('database.connections.sqlite.database');

            if (! $databasePath || ! File::exists($databasePath)) {
                throw new \RuntimeException('Não foi possível localizar o banco SQLite para criar o snapshot de emergência.');
            }

            File::copy($databasePath, $snapshotPath.'.sqlite');

            return;
        }

        throw new \RuntimeException("Snapshot de emergência não suportado para a conexão '{$connection}'.");
    }

    private function restoreDatabaseFromEmergencySnapshot(string $snapshotPath): void
    {
        $connection = config('database.default');

        if ($connection === 'sqlite') {
            $databasePath = config('database.connections.sqlite.database');
            $sqliteSnapshot = $snapshotPath.'.sqlite';

            if (! File::exists($sqliteSnapshot)) {
                throw new \RuntimeException('Snapshot SQLite de emergência não encontrado.');
            }

            File::copy($sqliteSnapshot, $databasePath);

            return;
        }

        $this->restoreDatabaseFromBackup($snapshotPath);
    }

    private function restoreDatabaseFromBackup(string $databaseBackupPath): void
    {
        $connection = config('database.default');

        if ($connection === 'pgsql') {
            $this->runDatabaseProcess($this->buildPostgresRestoreProcess($databaseBackupPath));

            return;
        }

        if ($connection === 'mysql' || $connection === 'mariadb') {
            $this->runDatabaseProcess($this->buildMysqlRestoreProcess($databaseBackupPath));

            return;
        }

        if ($connection === 'sqlite') {
            $this->restoreSqliteDatabase($databaseBackupPath);

            return;
        }

        throw new \RuntimeException("Restauração de banco não suportada para a conexão '{$connection}'.");
    }

    private function dumpPostgresDatabase(string $snapshotPath): void
    {
        $config = config('database.connections.pgsql');
        $binary = $this->resolveDatabaseBinary($config['dump']['dump_binary_path'] ?? '', 'pg_dump');

        $command = [
            $binary,
            '--file='.$snapshotPath,
            '--format=p',
            '--clean',
            '--if-exists',
        ];

        if (! empty($config['host'])) {
            $command[] = '--host='.$config['host'];
        }

        if (! empty($config['port'])) {
            $command[] = '--port='.$config['port'];
        }

        if (! empty($config['username'])) {
            $command[] = '--username='.$config['username'];
        }

        $command[] = $config['database'];

        $this->runDatabaseProcess(new Process($command, null, [
            'PGPASSWORD' => (string) ($config['password'] ?? ''),
        ]));
    }

    private function dumpMysqlDatabase(string $snapshotPath): void
    {
        $config = config('database.connections.mysql');
        $binary = $this->resolveDatabaseBinary($config['dump']['dump_binary_path'] ?? '', 'mysqldump');

        $command = [
            $binary,
            '--result-file='.$snapshotPath,
            '--single-transaction',
            '--skip-lock-tables',
            '--host='.$config['host'],
            '--port='.$config['port'],
            '--user='.$config['username'],
            $config['database'],
        ];

        $this->runDatabaseProcess(new Process($command, null, [
            'MYSQL_PWD' => (string) ($config['password'] ?? ''),
        ]));
    }

    private function buildPostgresRestoreProcess(string $sqlFilePath): Process
    {
        $config = config('database.connections.pgsql');
        $binary = $this->resolveDatabaseBinary($config['dump']['dump_binary_path'] ?? '', 'psql');

        $command = [
            $binary,
            '--set',
            'ON_ERROR_STOP=1',
        ];

        if (! empty($config['host'])) {
            $command[] = '--host='.$config['host'];
        }

        if (! empty($config['port'])) {
            $command[] = '--port='.$config['port'];
        }

        if (! empty($config['username'])) {
            $command[] = '--username='.$config['username'];
        }

        $command[] = '--dbname='.$config['database'];
        $command[] = '--file='.$sqlFilePath;

        return new Process($command, null, [
            'PGPASSWORD' => (string) ($config['password'] ?? ''),
        ]);
    }

    private function buildMysqlRestoreProcess(string $sqlFilePath): Process
    {
        $config = config('database.connections.mysql');
        $binary = $this->resolveDatabaseBinary($config['dump']['dump_binary_path'] ?? '', 'mysql');

        return new Process([
            $binary,
            '--host='.$config['host'],
            '--port='.$config['port'],
            '--user='.$config['username'],
            $config['database'],
        ], null, [
            'MYSQL_PWD' => (string) ($config['password'] ?? ''),
        ], File::get($sqlFilePath));
    }

    private function restoreSqliteDatabase(string $databaseBackupPath): void
    {
        $databasePath = config('database.connections.sqlite.database');

        if (! $databasePath || $databasePath === ':memory:') {
            throw new \RuntimeException('A restauração SQLite exige um arquivo físico de banco de dados.');
        }

        if (! File::exists($databaseBackupPath)) {
            throw new \RuntimeException('Arquivo SQLite do backup não encontrado.');
        }

        File::ensureDirectoryExists(dirname($databasePath));
        File::copy($databaseBackupPath, $databasePath);
    }

    private function runDatabaseProcess(Process $process): void
    {
        $process->setTimeout((float) config('database.connections.'.config('database.default').'.dump.timeout', 300));
        $process->run();

        if (! $process->isSuccessful()) {
            $output = trim($process->getErrorOutput().' '.$process->getOutput());
            throw new \RuntimeException('Falha no comando nativo de restauração: '.$output);
        }
    }

    private function resolveDatabaseBinary(string $binaryPath, string $binaryName): string
    {
        if ($binaryPath === '') {
            return $binaryName;
        }

        return rtrim($binaryPath, '\\/').DIRECTORY_SEPARATOR.$binaryName;
    }

    private function notifyAdminAction(string $title, array $details = [], string $status = 'info'): void
    {
        app(TelegramNotifier::class)->notifyAdministrativeAction($title, $details, $status);
    }
}

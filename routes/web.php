<?php

use App\Http\Controllers\AtaController;
use App\Http\Controllers\AtoController;
use App\Http\Controllers\AttendanceColumnController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\BackupController;
use App\Http\Controllers\CaixaController;
use App\Http\Controllers\ClassesController;
use App\Http\Controllers\ClubBackupController;
use App\Http\Controllers\ClubController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DesbravadorController;
use App\Http\Controllers\EspecialidadeController;
use App\Http\Controllers\EventoController;
use App\Http\Controllers\FrequenciaController;
use App\Http\Controllers\InvitationController;
use App\Http\Controllers\LegalController;
use App\Http\Controllers\MensalidadeController;
use App\Http\Controllers\PatrimonioController;
use App\Http\Controllers\PlatformController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RankingController;
use App\Http\Controllers\RelatorioController;
use App\Http\Controllers\UnidadeController;
use App\Http\Controllers\UsuarioController;
use App\Http\Middleware\EnsureClubContextForPlatformAdmin;
use App\Http\Middleware\EnsureClubIsActive;
use App\Http\Middleware\EnsureTermosAceitos;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Health check — para load balancers, uptime monitors e alertas de disponibilidade.
Route::get('/health', function () {
    // Banco de dados
    try {
        \Illuminate\Support\Facades\DB::connection()->getPdo();
        $dbStatus = 'ok';
    } catch (\Exception) {
        $dbStatus = 'error';
    }

    // Cache
    try {
        \Illuminate\Support\Facades\Cache::put('health_check', true, 5);
        $cacheStatus = \Illuminate\Support\Facades\Cache::get('health_check') === true ? 'ok' : 'error';
    } catch (\Exception) {
        $cacheStatus = 'error';
    }

    // Fila — jobs pendentes
    try {
        $queueSize = \Illuminate\Support\Facades\DB::table('jobs')->count();
    } catch (\Exception) {
        $queueSize = -1;
    }

    // Jobs falhos recentes (últimas 24h)
    try {
        $failedJobs = \Illuminate\Support\Facades\DB::table('failed_jobs')
            ->where('failed_at', '>=', now()->subDay())
            ->count();
    } catch (\Exception) {
        $failedJobs = -1;
    }

    $isHealthy = $dbStatus === 'ok';
    $isDegraded = $queueSize > 100 || $failedJobs > 10;
    $overallStatus = $isHealthy ? ($isDegraded ? 'degraded' : 'ok') : 'error';
    $httpStatus = $isHealthy ? 200 : 503;

    return response()->json([
        'status' => $overallStatus,
        'database' => $dbStatus,
        'cache' => $cacheStatus,
        'queue_size' => $queueSize,
        'failed_jobs_24h' => $failedJobs,
        'timestamp' => now()->toIso8601String(),
    ], $httpStatus);
})->name('health');

// Publico
Route::get('/', function () {
    return view('welcome');
});

// Páginas legais — acessíveis sem autenticação (LGPD Art. 9 — transparência)
Route::get('/privacidade', [LegalController::class, 'privacidade'])->name('legal.privacidade');
Route::get('/termos', [LegalController::class, 'termos'])->name('legal.termos');

// Registro via convite
Route::get('/register-invite', [RegisteredUserController::class, 'create'])->name('register.invite');
Route::post('/register-invite', [RegisteredUserController::class, 'store'])->name('register.store_invite');

// Aceite de termos para usuários existentes (LGPD). Fica FORA do grupo principal
// (sem EnsureTermosAceitos) para que a própria tela de aceite seja acessível e
// não gere loop de redirecionamento. Basta estar autenticado.
Route::middleware('auth')->group(function () {
    Route::get('/aceitar-termos', [LegalController::class, 'mostrarAceiteTermos'])->name('termos.aceitar');
    Route::post('/aceitar-termos', [LegalController::class, 'registrarAceiteTermos'])->name('termos.aceitar.store');
});

// Area restrita
Route::middleware(['auth', 'verified', EnsureTermosAceitos::class, EnsureClubIsActive::class, EnsureClubContextForPlatformAdmin::class])->group(function () {
    // 1. Dashboard e perfil
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // 2. Gestao de acessos (master + permissao adicional)
    Route::middleware('can:gestao-acessos')->group(function () {
        // Usuarios
        Route::resource('usuarios', UsuarioController::class)->except(['show']);

        // Convites
        Route::prefix('invites')->name('invites.')->group(function () {
            Route::get('/', [InvitationController::class, 'index'])->name('index');
            Route::get('/create', [InvitationController::class, 'create'])->name('create');
            Route::post('/', [InvitationController::class, 'store'])->name('store');
            Route::delete('/{invite}', [InvitationController::class, 'destroy'])->name('destroy');
            Route::post('/{invite}/resend', [InvitationController::class, 'resend'])->name('resend');
        });
    });

    // 2.1 Painel de Plataforma (Super Admin / cross-tenant)
    Route::middleware('can:platform-admin')
        ->prefix('platform')
        ->name('platform.')
        ->group(function () {
            Route::get('/', [PlatformController::class, 'index'])->name('index');
            Route::get('/observabilidade', [PlatformController::class, 'observabilidade'])->name('observabilidade');
            Route::get('/clubs/create', [PlatformController::class, 'createClub'])->name('clubs.create');
            Route::post('/clubs', [PlatformController::class, 'storeClub'])->name('clubs.store');
            Route::post('/clubs/{club}/enter', [PlatformController::class, 'enterClub'])->name('enter');
            Route::post('/clubs/exit', [PlatformController::class, 'exitClub'])->name('exit');
            Route::get('/clubs/{club}/export', [PlatformController::class, 'exportClub'])->name('export');
            Route::post('/clubs/{club}/toggle-active', [PlatformController::class, 'toggleActive'])->name('toggle-active');
            Route::delete('/clubs/{club}', [PlatformController::class, 'destroy'])->name('clubs.destroy');
        });

    // 3. Backups completos do banco — responsabilidade da PLATAFORMA (super admin),
    //    não do master de clube. Master de clube usa a exportação por clube.
    Route::middleware('can:platform-admin')->group(function () {
        Route::prefix('backups')->name('backups.')->group(function () {
            Route::get('/', [BackupController::class, 'index'])->name('index');
            Route::post('/', [BackupController::class, 'store'])->name('store');
            Route::get('/download', [BackupController::class, 'download'])->name('download');
            Route::delete('/destroy', [BackupController::class, 'destroy'])->name('destroy');
            Route::post('/import', [BackupController::class, 'import'])->name('import');
            Route::post('/restore', [BackupController::class, 'restore'])->name('restore');
        });
    });

    // 3.1 Exportação JSON dos dados do clube (migração entre instalações) — master.
    Route::middleware('can:master')->group(function () {
        Route::get('/clube/exportar-dados', [ClubController::class, 'exportarDados'])->name('club.export');
    });

    // 3.2 Backups isolados por clube — master do próprio clube OU platform-admin
    //     impersonando um clube (ClubBackupController valida o contexto internamente).
    Route::prefix('backups/clube')->name('club-backups.')->group(function () {
        Route::get('/', [ClubBackupController::class, 'index'])->name('index');
        Route::post('/', [ClubBackupController::class, 'store'])->name('store');
        Route::get('/download', [ClubBackupController::class, 'download'])->name('download');
        Route::delete('/destroy', [ClubBackupController::class, 'destroy'])->name('destroy');
        Route::post('/restore', [ClubBackupController::class, 'restore'])->name('restore');
    });

    // 4. Secretaria (gestao de membros, clube e eventos CRUD)
    Route::middleware('can:secretaria')->group(function () {
        // Configuracoes do clube
        Route::get('/clube', [ClubController::class, 'edit'])->name('club.edit');
        Route::patch('/clube', [ClubController::class, 'update'])->name('club.update');
        Route::delete('/clube/logo', [ClubController::class, 'removeLogo'])->name('club.remove_logo');

        // Documentos oficiais
        Route::get('atas/{ata}/imprimir', [AtaController::class, 'print'])->name('atas.print');
        Route::resource('atas', AtaController::class);
        Route::resource('atos', AtoController::class);

        // Gestao de pessoas
        Route::resource('desbravadores', DesbravadorController::class)->parameters(['desbravadores' => 'desbravador']);
        Route::delete('desbravadores/{desbravador}/foto', [DesbravadorController::class, 'removerFoto'])->name('desbravadores.remover-foto');
        Route::post('desbravadores/{desbravador}/avancar-classe', [DesbravadorController::class, 'avancarClasse'])->name('desbravadores.avancar-classe');
        Route::get('desbravadores/{desbravador}/exportar-dados', [DesbravadorController::class, 'exportarDadosLgpd'])->name('desbravadores.exportar-dados');
        Route::resource('unidades', UnidadeController::class)->except(['index', 'show']);
        Route::patch('unidades/{unidade}/toggle-ranking', [UnidadeController::class, 'toggleRanking'])->name('unidades.toggle-ranking');

        // Criacao de eventos
        Route::get('/eventos/create', [EventoController::class, 'create'])->name('eventos.create');
        Route::post('/eventos', [EventoController::class, 'store'])->name('eventos.store');
        Route::get('/eventos/{evento}/edit', [EventoController::class, 'edit'])->name('eventos.edit');
        Route::put('/eventos/{evento}', [EventoController::class, 'update'])->name('eventos.update');
        Route::delete('/eventos/{evento}', [EventoController::class, 'destroy'])->name('eventos.destroy');
    });

    // 5. Visualizacao geral (conselheiros e outros cargos)
    Route::get('/unidades', [UnidadeController::class, 'index'])->name('unidades.index');
    Route::get('/unidades/{unidade}', [UnidadeController::class, 'show'])->name('unidades.show');
    Route::get('/desbravadores/{desbravador}', [DesbravadorController::class, 'show'])->name('desbravadores.show');

    // 6. Pedagogico — clubes CONSOMEM o catálogo global (leitura) e registram o
    //    progresso dos seus desbravadores. A EDIÇÃO do catálogo é da plataforma (6.1).
    Route::middleware('can:pedagogico')->group(function () {
        Route::get('especialidades', [EspecialidadeController::class, 'index'])->name('especialidades.index');
        // whereNumber evita que /especialidades/create case com este wildcard
        // (a rota create, no grupo platform-admin, é registrada depois).
        Route::get('especialidades/{especialidade}', [EspecialidadeController::class, 'show'])->name('especialidades.show')->whereNumber('especialidade');
        Route::get('/especialidades/{especialidade}/historico', [EspecialidadeController::class, 'historico'])->name('especialidades.historico');

        // Progresso do desbravador (por clube) — permanece com os clubes.
        Route::get('/desbravadores/{desbravador}/especialidades', [DesbravadorController::class, 'gerenciarEspecialidades'])->name('desbravadores.especialidades');
        Route::post('/desbravadores/{desbravador}/especialidades', [DesbravadorController::class, 'salvarEspecialidades'])->name('desbravadores.salvar-especialidades');
        Route::delete('/desbravadores/{desbravador}/especialidades/{especialidade}', [DesbravadorController::class, 'removerEspecialidade'])->name('desbravadores.remover-especialidade');

        Route::prefix('classes')->name('classes.')->group(function () {
            Route::get('/', [ClassesController::class, 'index'])->name('index');
            Route::get('/{classe}', [ClassesController::class, 'show'])->name('show');
            Route::post('/toggle-requisito', [ClassesController::class, 'toggle'])->name('toggle'); // progresso
        });

        Route::prefix('frequencia')->name('frequencia.')->group(function () {
            Route::get('/', [FrequenciaController::class, 'index'])->name('index');
            Route::get('/chamada', [FrequenciaController::class, 'create'])->name('create');
            Route::post('/store', [FrequenciaController::class, 'store'])->name('store');
            Route::delete('/data/{data}', [FrequenciaController::class, 'destroyData'])->name('destroy-data');
        });
    });

    // 6.1 Gestão do CATÁLOGO GLOBAL (especialidades, requisitos, requisitos de
    //     classe) — compartilhado entre todos os clubes, então editável SÓ pela
    //     plataforma. Clubes apenas consomem (grupo 6).
    Route::middleware('can:platform-admin')->group(function () {
        Route::resource('especialidades', EspecialidadeController::class)->only(['create', 'store', 'edit', 'update', 'destroy']);
        Route::post('/especialidades/{especialidade}/requisitos', [EspecialidadeController::class, 'storeRequisito'])->name('especialidades.requisitos.store');
        Route::put('/especialidades/{especialidade}/requisitos/{requisito}', [EspecialidadeController::class, 'updateRequisito'])->name('especialidades.requisitos.update');
        Route::delete('/especialidades/{especialidade}/requisitos/{requisito}', [EspecialidadeController::class, 'destroyRequisito'])->name('especialidades.requisitos.destroy');

        Route::prefix('classes')->name('classes.')->group(function () {
            Route::post('/{classe}/requisitos', [ClassesController::class, 'storeRequisito'])->name('requisitos.store');
            Route::put('/{classe}/requisitos/{requisito}', [ClassesController::class, 'updateRequisito'])->name('requisitos.update');
            Route::delete('/{classe}/requisitos/{requisito}', [ClassesController::class, 'destroyRequisito'])->name('requisitos.destroy');
        });
    });

    Route::middleware('can:gerenciar-colunas-chamada')->prefix('frequencia/colunas')->name('frequencia.columns.')->group(function () {
        Route::get('/', [AttendanceColumnController::class, 'index'])->name('index');
        Route::put('/', [AttendanceColumnController::class, 'update'])->name('update');
        Route::delete('/{attendanceColumn}', [AttendanceColumnController::class, 'destroy'])->name('destroy');
    });

    // 7. Financeiro
    Route::middleware('can:financeiro')->group(function () {
        Route::resource('caixa', CaixaController::class);
        Route::resource('patrimonio', PatrimonioController::class);
        Route::post('patrimonio/{patrimonio}/manutencoes', [PatrimonioController::class, 'storeManutencao'])->name('patrimonio.manutencoes.store');
        Route::delete('patrimonio/{patrimonio}/manutencoes/{manutencao}', [PatrimonioController::class, 'destroyManutencao'])->name('patrimonio.manutencoes.destroy');

        Route::get('mensalidades', [MensalidadeController::class, 'index'])->name('mensalidades.index');
        Route::post('mensalidades/gerar', [MensalidadeController::class, 'gerarMassivo'])->name('mensalidades.gerar');
        Route::post('mensalidades/{id}/pagar', [MensalidadeController::class, 'pagar'])->name('mensalidades.pagar');
    });

    // 8. Eventos (visualizacao e inscricao)
    Route::middleware('can:eventos')->group(function () {
        Route::get('/eventos', [EventoController::class, 'index'])->name('eventos.index');
        Route::get('/eventos/{evento}', [EventoController::class, 'show'])->name('eventos.show');
        Route::post('eventos/{evento}/inscrever', [EventoController::class, 'inscrever'])->name('eventos.inscrever');
        Route::post('eventos/{evento}/inscrever-lote', [EventoController::class, 'inscreverEmLote'])->name('eventos.inscrever-lote');
        Route::delete('eventos/{evento}/inscricao/{desbravador}', [EventoController::class, 'removerInscricao'])->name('eventos.remover-inscricao');
        Route::patch('eventos/{evento}/inscricao/{desbravador}', [EventoController::class, 'atualizarStatus'])->name('eventos.status')->middleware('can:financeiro');
        Route::get('eventos/{evento}/autorizacao/{desbravador}', [EventoController::class, 'gerarAutorizacao'])->name('eventos.autorizacao');
    });

    // 9. Ranking
    Route::prefix('ranking')->name('ranking.')->middleware('can:relatorios')->group(function () {
        Route::get('/unidades', [RankingController::class, 'unidades'])->name('unidades');
        Route::get('/desbravadores', [RankingController::class, 'desbravadores'])->name('desbravadores');
        Route::post('/snapshot', [RankingController::class, 'salvarSnapshot'])->name('salvar-snapshot');
        Route::get('/snapshot/{scope}', [RankingController::class, 'verSnapshot'])->name('ver-snapshot')->where('scope', 'unidades|desbravadores');
    });

    // 10. Relatorios
    Route::prefix('relatorios')->name('relatorios.')->middleware('can:relatorios')->group(function () {
        Route::get('/', [RelatorioController::class, 'index'])->name('index');
        Route::match(['get', 'post'], '/gerar-personalizado', [RelatorioController::class, 'gerarPersonalizado'])->middleware('throttle:relatorios')->name('custom');
        Route::get('/autorizacao/{desbravador}', [RelatorioController::class, 'autorizacao'])->name('autorizacao');
        Route::get('/carteirinha/{desbravador}', [RelatorioController::class, 'carteirinha'])->name('carteirinha');
        Route::get('/ficha-medica/{desbravador}', [RelatorioController::class, 'fichaMedica'])->name('ficha-medica');

        Route::middleware('can:financeiro')->group(function () {
            Route::get('/financeiro', [RelatorioController::class, 'financeiro'])->name('financeiro');
            Route::get('/patrimonio', [RelatorioController::class, 'patrimonio'])->name('patrimonio');
        });

        Route::get('/downloads', [RelatorioController::class, 'downloads'])->name('downloads');
        Route::get('/downloads/{relatorio}', [RelatorioController::class, 'download'])->name('download');
    });

    // Manual do sistema
    Route::view('/manual-sistema', 'manual-sistema')->name('manual.sistema');

    // Aba sobre o sistema
    Route::view('/sobre', 'sobre')->name('sobre');
});

require __DIR__.'/auth.php';

<?php

use App\Http\Controllers\AtaController;
use App\Http\Controllers\AtoController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\CaixaController;
use App\Http\Controllers\ClubController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DesbravadorController;
use App\Http\Controllers\EspecialidadeController;
use App\Http\Controllers\EventoController;
use App\Http\Controllers\InvitationController;
use App\Http\Controllers\MensalidadeController;
use App\Http\Controllers\PatrimonioController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProgressoController;
use App\Http\Controllers\RelatorioController;
use App\Http\Controllers\UnidadeController;
use App\Http\Controllers\UsuarioController;
use Illuminate\Support\Facades\Route;

// --- ROTA PÚBLICA ---
Route::get('/', function () {
    return view('welcome');
});

// ROTAS PÚBLICAS (Registro via Convite)
Route::get('/register-invite', [RegisteredUserController::class, 'create'])->name('register.invite');
Route::post('/register-invite', [RegisteredUserController::class, 'store'])->name('register.store_invite');

// --- DASHBOARD (Acesso Comum a Todos) ---
Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

// --- GRUPO PROTEGIDO (REQUER LOGIN) ---
Route::middleware('auth')->group(function () {

    // --- PERFIL DO USUÁRIO (Acesso Comum) ---
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // GESTÃO DE USUÁRIOS E CONVITES (MASTER)
    Route::middleware('can:master')->group(function () {
        // CRUD de usuários existentes
        Route::resource('usuarios', UsuarioController::class);

        // CRUD de Convites
        Route::get('/invites', [InvitationController::class, 'index'])->name('invites.index');
        Route::get('/invites/create', [InvitationController::class, 'create'])->name('invites.create');
        Route::post('/invites', [InvitationController::class, 'store'])->name('invites.store');
        Route::delete('/invites/{invite}', [InvitationController::class, 'destroy'])->name('invites.destroy');
    });

    // --- MÓDULO SECRETARIA (Diretor e Secretário) ---
    Route::middleware('can:secretaria')->group(function () {
        // Dados do Clube
        Route::get('/clube', [ClubController::class, 'edit'])->name('club.edit');
        Route::patch('/clube', [ClubController::class, 'update'])->name('club.update');
        Route::delete('/clube/logo', [ClubController::class, 'destroyLogo'])->name('club.logo.destroy');

        // Documentos Oficiais
        Route::resource('atas', AtaController::class);
        Route::resource('atos', AtoController::class);

        // Gestão Plena de Desbravadores e Unidades (Criação/Edição)
        Route::resource('desbravadores', DesbravadorController::class)
            ->parameters(['desbravadores' => 'desbravador']);

        Route::resource('unidades', UnidadeController::class)->except(['index', 'show']);
    });

    // --- VISUALIZAÇÃO DE MEMBROS E UNIDADES (Aberto também a Conselheiros) ---
    // A rota INDEX e SHOW é permitida para conselheiros, mas o EDIT/CREATE só para secretaria (acima)
    Route::get('/unidades', [UnidadeController::class, 'index'])->name('unidades.index');
    Route::get('/unidades/{unidade}', [UnidadeController::class, 'show'])->name('unidades.show');

    // Se o conselheiro precisar ver o perfil do desbravador (mas não editar todos os dados sensíveis)
    // Por enquanto, deixamos o 'show' do desbravador acessível se souber o ID,
    // mas a edição completa está protegida no resource acima.
    Route::get('/desbravadores/{desbravador}', [DesbravadorController::class, 'show'])->name('desbravadores.show');

    // --- MÓDULO PEDAGÓGICO (Instrutores e Conselheiros também acessam) ---
    Route::middleware('can:pedagogico')->group(function () {
        // Especialidades (CRUD Geral)
        Route::resource('especialidades', EspecialidadeController::class);

        // Gestão de Especialidades do Membro
        Route::get('/desbravadores/{desbravador}/especialidades', [DesbravadorController::class, 'gerenciarEspecialidades'])
            ->name('desbravadores.especialidades');
        Route::post('/desbravadores/{desbravador}/especialidades', [DesbravadorController::class, 'salvarEspecialidades'])
            ->name('desbravadores.salvar-especialidades');
        Route::delete('/desbravadores/{desbravador}/especialidades/{especialidade}', [DesbravadorController::class, 'removerEspecialidade'])
            ->name('desbravadores.remover-especialidade');

        // Classes Progressivas
        Route::get('/desbravadores/{desbravador}/progresso', [ProgressoController::class, 'index'])->name('progresso.index');
        Route::post('/desbravadores/{desbravador}/progresso/toggle', [ProgressoController::class, 'toggle'])->name('progresso.toggle');

        // Frequência (Agrupadas)
        Route::prefix('frequencia')->name('frequencia.')->group(function () {
            // Nova rota para ver o histórico (Visualização)
            Route::get('/', [App\Http\Controllers\FrequenciaController::class, 'index'])->name('index');

            // Rota antiga para fazer a chamada (Ação)
            Route::get('/chamada', [App\Http\Controllers\FrequenciaController::class, 'create'])->name('create');
            Route::post('/store', [App\Http\Controllers\FrequenciaController::class, 'store'])->name('store');
        });
    });

    // --- MÓDULO FINANCEIRO (Tesoureiro e Diretor) ---
    Route::middleware('can:financeiro')->group(function () {
        Route::resource('caixa', CaixaController::class);
        Route::resource('patrimonio', PatrimonioController::class);

        // Mensalidades
        Route::get('mensalidades', [MensalidadeController::class, 'index'])->name('mensalidades.index');
        Route::post('mensalidades/gerar', [MensalidadeController::class, 'gerarMassivo'])->name('mensalidades.gerar');
        Route::post('mensalidades/{id}/pagar', [MensalidadeController::class, 'pagar'])->name('mensalidades.pagar');
    });

    // --- MÓDULO DE EVENTOS ---
    // 1. Listagem Geral (Pode ficar aqui)
    Route::get('/eventos', [EventoController::class, 'index'])->name('eventos.index');
    // 2. MOVA ESTE BLOCO PARA CIMA (Rotas específicas primeiro)
    Route::middleware('can:eventos')->group(function () {
        Route::get('/eventos/create', [EventoController::class, 'create'])->name('eventos.create'); // <- Agora o Laravel acha esta primeiro!
        Route::post('/eventos', [EventoController::class, 'store'])->name('eventos.store');
        Route::get('/eventos/{evento}/edit', [EventoController::class, 'edit'])->name('eventos.edit');
        Route::put('/eventos/{evento}', [EventoController::class, 'update'])->name('eventos.update');
        Route::delete('/eventos/{evento}', [EventoController::class, 'destroy'])->name('eventos.destroy');

        Route::post('eventos/{evento}/inscrever', [EventoController::class, 'inscrever'])->name('eventos.inscrever');
        Route::delete('eventos/{evento}/inscricao/{desbravador}', [EventoController::class, 'removerInscricao'])->name('eventos.remover-inscricao');
        Route::patch('eventos/{evento}/inscricao/{desbravador}', [EventoController::class, 'atualizarStatus'])->name('eventos.status');

        Route::get('eventos/{evento}/autorizacao/{desbravador}', [EventoController::class, 'gerarAutorizacao'])->name('eventos.autorizacao');
    });
    // 3. Rota Genérica / Wildcard (DEVE SER A ÚLTIMA DE EVENTOS)
    // Qualquer coisa que não for 'create' cairá aqui (ex: /eventos/1)
    Route::get('/eventos/{evento}', [EventoController::class, 'show'])->name('eventos.show');

    // Rotas de Ranking (Agrupadas)
    Route::prefix('ranking')->name('ranking.')->group(function () {
        Route::get('/unidades', [App\Http\Controllers\RankingController::class, 'unidades'])->name('unidades');
        Route::get('/desbravadores', [App\Http\Controllers\RankingController::class, 'desbravadores'])->name('desbravadores');
    });

    // --- CENTRAL DE RELATÓRIOS (Acesso Misto) ---
    Route::prefix('relatorios')->name('relatorios.')->group(function () {
        // Painel Principal
        Route::get('/', [RelatorioController::class, 'index'])->name('index');
        Route::post('/gerar-personalizado', [RelatorioController::class, 'gerarPersonalizado'])->name('custom');

        // Financeiro (Protegido)
        Route::get('/financeiro', [RelatorioController::class, 'financeiro'])
            ->name('financeiro')
            ->middleware('can:financeiro');

        // Patrimônio (Protegido)
        Route::get('/patrimonio', [RelatorioController::class, 'patrimonio'])
            ->name('patrimonio')
            ->middleware('can:financeiro');

        // Documentos Pessoais (Abertos para quem pode ver desbravadores)
        Route::get('/autorizacao/{desbravador}', [RelatorioController::class, 'autorizacao'])->name('autorizacao');
        Route::get('/carteirinha/{desbravador}', [RelatorioController::class, 'carteirinha'])->name('carteirinha');
        Route::get('/ficha-medica/{desbravador}', [RelatorioController::class, 'fichaMedica'])->name('ficha-medica');
    });
});

require __DIR__.'/auth.php';

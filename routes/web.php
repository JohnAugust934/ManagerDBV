<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UnidadeController;
use App\Http\Controllers\DesbravadorController;
use App\Http\Controllers\EspecialidadeController;
use App\Http\Controllers\CaixaController;
use App\Http\Controllers\MensalidadeController;
use App\Http\Controllers\PatrimonioController;
use App\Http\Controllers\AtaController;
use App\Http\Controllers\AtoController;
use App\Http\Controllers\RelatorioController;
use App\Http\Controllers\ClubController;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProgressoController;
use App\Http\Controllers\EventoController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    // --- PERFIL DO USUÁRIO ---
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // --- GESTÃO DO CLUBE ---
    Route::get('/clube', [ClubController::class, 'edit'])->name('club.edit');
    Route::patch('/clube', [ClubController::class, 'update'])->name('club.update');
    Route::delete('/clube/logo', [ClubController::class, 'destroyLogo'])->name('club.logo.destroy');

    // --- CADASTROS BÁSICOS (EXISTENTES) ---
    Route::resource('unidades', UnidadeController::class);
    Route::resource('desbravadores', DesbravadorController::class)
        ->parameters(['desbravadores' => 'desbravador']);
    Route::resource('especialidades', EspecialidadeController::class);

    // Gestão de Especialidades
    Route::get('/desbravadores/{desbravador}/especialidades', [DesbravadorController::class, 'gerenciarEspecialidades'])
        ->name('desbravadores.especialidades');

    Route::post('/desbravadores/{desbravador}/especialidades', [DesbravadorController::class, 'salvarEspecialidades'])
        ->name('desbravadores.salvar-especialidades');

    Route::delete('/desbravadores/{desbravador}/especialidades/{especialidade}', [DesbravadorController::class, 'removerEspecialidade'])
        ->name('desbravadores.remover-especialidade');

    // --- FINANCEIRO ---
    Route::resource('caixa', CaixaController::class);
    Route::get('mensalidades', [MensalidadeController::class, 'index'])->name('mensalidades.index');
    Route::post('mensalidades/gerar', [MensalidadeController::class, 'gerarMassivo'])->name('mensalidades.gerar');
    Route::post('mensalidades/{id}/pagar', [MensalidadeController::class, 'pagar'])->name('mensalidades.pagar');

    // --- PATRIMÔNIO ---
    Route::resource('patrimonio', PatrimonioController::class);

    // --- SECRETARIA ---
    Route::resource('atas', AtaController::class);
    Route::resource('atos', AtoController::class);

    // Módulo de Relatórios
    Route::prefix('relatorios')->name('relatorios.')->group(function () {
        // Painel Principal
        Route::get('/', [RelatorioController::class, 'index'])->name('index');

        // Gerador Personalizado
        Route::post('/gerar-personalizado', [RelatorioController::class, 'gerarPersonalizado'])->name('custom');

        // Relatórios Específicos (Já existentes + Novos acessos)
        Route::get('/financeiro', [RelatorioController::class, 'financeiro'])->name('financeiro');
        Route::get('/patrimonio', [RelatorioController::class, 'patrimonio'])->name('patrimonio');

        // Rotas individuais (mantidas para acesso via perfil, mas não usadas na central geral de listas)
        Route::get('/autorizacao/{desbravador}', [RelatorioController::class, 'autorizacao'])->name('autorizacao');
        Route::get('/carteirinha/{desbravador}', [RelatorioController::class, 'carteirinha'])->name('carteirinha');
        Route::get('/ficha-medica/{desbravador}', [RelatorioController::class, 'fichaMedica'])->name('ficha-medica');
    });

    // --- FREQUÊNCIA ---
    Route::get('/frequencia/chamada', [App\Http\Controllers\FrequenciaController::class, 'create'])->name('frequencia.create');
    Route::post('/frequencia/chamada', [App\Http\Controllers\FrequenciaController::class, 'store'])->name('frequencia.store');

    Route::get('/desbravadores/{desbravador}/progresso', [ProgressoController::class, 'index'])->name('progresso.index');
    Route::post('/desbravadores/{desbravador}/progresso/toggle', [ProgressoController::class, 'toggle'])->name('progresso.toggle');

    // Gestão de Eventos
    Route::resource('eventos', EventoController::class);
    Route::post('eventos/{evento}/inscrever', [EventoController::class, 'inscrever'])->name('eventos.inscrever');
    Route::delete('eventos/{evento}/inscricao/{desbravador}', [EventoController::class, 'removerInscricao'])->name('eventos.remover-inscricao');
    Route::patch('eventos/{evento}/inscricao/{desbravador}', [EventoController::class, 'atualizarStatus'])->name('eventos.status');
    Route::get('eventos/{evento}/autorizacao/{desbravador}', [EventoController::class, 'gerarAutorizacao'])->name('eventos.autorizacao');

    // --- ÁREA DO ADMINISTRADOR MASTER ---
    // Rotas para gerar convites (Apenas para o usuário Master)
    Route::get('/master/invites', function () {
        if (!auth()->user()->is_master) {
            abort(403, 'Acesso restrito ao Master Admin.');
        }
        $invites = \App\Models\Invitation::latest()->get();
        return view('admin.invites', compact('invites'));
    })->name('master.invites');

    Route::post('/master/invites', function (Request $request) {
        if (!auth()->user()->is_master) {
            abort(403);
        }
        $request->validate(['email' => 'required|email|unique:users,email']);

        $token = \Illuminate\Support\Str::random(32);
        \App\Models\Invitation::create([
            'email' => $request->email,
            'token' => $token
        ]);

        return back()->with('success', "Convite gerado com sucesso!");
    })->name('master.invites.store');
});

require __DIR__ . '/auth.php';

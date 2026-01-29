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

    // --- ESPECIALIDADES DO DESBRAVADOR ---
    Route::get('desbravadores/{id}/especialidades', [DesbravadorController::class, 'gerenciarEspecialidades'])->name('desbravadores.especialidades');
    Route::post('desbravadores/{id}/especialidades', [DesbravadorController::class, 'salvarEspecialidade'])->name('desbravadores.especialidades.store');
    Route::delete('desbravadores/{id}/especialidades/{especialidade_id}', [DesbravadorController::class, 'removerEspecialidade'])->name('desbravadores.especialidades.destroy');

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

    // --- RELATÓRIOS (PDF) ---
    Route::get('relatorios/autorizacao/{id}', [RelatorioController::class, 'autorizacaoSaida'])->name('relatorios.autorizacao');
    Route::get('relatorios/financeiro', [RelatorioController::class, 'financeiro'])->name('relatorios.financeiro');
    Route::get('relatorios/patrimonio', [RelatorioController::class, 'patrimonio'])->name('relatorios.patrimonio');

    // --- FREQUÊNCIA ---
    Route::get('/frequencia/chamada', [App\Http\Controllers\FrequenciaController::class, 'create'])->name('frequencia.create');
    Route::post('/frequencia/chamada', [App\Http\Controllers\FrequenciaController::class, 'store'])->name('frequencia.store');

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

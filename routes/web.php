<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UnidadeController;
use App\Http\Controllers\DesbravadorController;
use App\Http\Controllers\EspecialidadeController;
use App\Http\Controllers\CaixaController;
use App\Http\Controllers\MensalidadeController;
use App\Http\Controllers\PatrimonioController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // --- CADASTROS BÁSICOS ---
    Route::resource('unidades', UnidadeController::class);
    Route::resource('desbravadores', DesbravadorController::class);
    Route::resource('especialidades', EspecialidadeController::class);

    // --- ESPECIALIDADES DO DESBRAVADOR ---
    Route::get('desbravadores/{id}/especialidades', [DesbravadorController::class, 'gerenciarEspecialidades'])->name('desbravadores.especialidades');
    Route::post('desbravadores/{id}/especialidades', [DesbravadorController::class, 'salvarEspecialidade'])->name('desbravadores.especialidades.store');
    Route::delete('desbravadores/{id}/especialidades/{especialidade_id}', [DesbravadorController::class, 'removerEspecialidade'])->name('desbravadores.especialidades.destroy');

    // --- FINANCEIRO ---
    Route::resource('caixa', CaixaController::class);

    // Rotas de Mensalidade
    Route::get('mensalidades', [MensalidadeController::class, 'index'])->name('mensalidades.index');
    Route::post('mensalidades/gerar', [MensalidadeController::class, 'gerarMassivo'])->name('mensalidades.gerar');
    Route::post('mensalidades/{id}/pagar', [MensalidadeController::class, 'pagar'])->name('mensalidades.pagar');

    // --- PATRIMÔNIO ---
    Route::resource('patrimonio', PatrimonioController::class);
});

require __DIR__ . '/auth.php';

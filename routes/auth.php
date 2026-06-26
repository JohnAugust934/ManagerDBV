<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\ConfirmablePasswordController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\EmailVerificationPromptController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasskeyAutenticacaoController;
use App\Http\Controllers\Auth\PasskeyController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\VerifyEmailController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {

    // --- ALTERAÇÃO: Rota de registro agora exige token na URL ---
    Route::get('register', [RegisteredUserController::class, 'create'])
        ->name('register');

    Route::post('register', [RegisteredUserController::class, 'store']);
    // ------------------------------------------------------------

    Route::get('login', [AuthenticatedSessionController::class, 'create'])
        ->name('login');

    Route::post('login', [AuthenticatedSessionController::class, 'store']);

    // Login por passkey (asserção WebAuthn) — alternativa SEM senha ao login do
    // Breeze, que permanece intacto. Acessível ao visitante.
    Route::post('passkeys/login/opcoes', [PasskeyAutenticacaoController::class, 'options'])
        ->name('passkeys.login.options');
    Route::post('passkeys/login', [PasskeyAutenticacaoController::class, 'store'])
        ->middleware('throttle:6,1')
        ->name('passkeys.login');

    Route::get('forgot-password', [PasswordResetLinkController::class, 'create'])
        ->name('password.request');

    Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])
        ->name('password.email');

    Route::get('reset-password/{token}', [NewPasswordController::class, 'create'])
        ->name('password.reset');

    Route::post('reset-password', [NewPasswordController::class, 'store'])
        ->name('password.store');
});

Route::middleware('auth')->group(function () {
    Route::get('verify-email', EmailVerificationPromptController::class)
        ->name('verification.notice');

    Route::get('verify-email/{id}/{hash}', VerifyEmailController::class)
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');

    Route::post('email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
        ->middleware('throttle:6,1')
        ->name('verification.send');

    Route::get('confirm-password', [ConfirmablePasswordController::class, 'show'])
        ->name('password.confirm');

    Route::post('confirm-password', [ConfirmablePasswordController::class, 'store']);

    Route::put('password', [PasswordController::class, 'update'])->name('password.update');

    // Gerência das passkeys do próprio usuário (registro aditivo, listagem,
    // remoção). A senha continua válida — passkey é método alternativo.
    Route::get('passkeys', [PasskeyController::class, 'index'])->name('passkeys.index');
    Route::post('passkeys/opcoes', [PasskeyController::class, 'options'])->name('passkeys.options');
    Route::post('passkeys', [PasskeyController::class, 'store'])->name('passkeys.store');
    Route::delete('passkeys/{id}', [PasskeyController::class, 'destroy'])
        ->whereNumber('id')
        ->name('passkeys.destroy');

    // Dispensa o banner de convite para cadastrar passkey (persistido por usuário).
    Route::post('passkeys/banner/dispensar', [PasskeyController::class, 'dispensarBanner'])
        ->name('passkeys.banner.dismiss');

    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])
        ->name('logout');
});

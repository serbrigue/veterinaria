<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\ConfirmablePasswordController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\EmailVerificationPromptController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\VerifyEmailController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('registro', [RegisteredUserController::class, 'create'])
                ->name('registrarse');

    Route::post('registro', [RegisteredUserController::class, 'store']);

    Route::get('iniciar-sesion', [AuthenticatedSessionController::class, 'create'])
                ->name('iniciar-sesion');

    Route::post('iniciar-sesion', [AuthenticatedSessionController::class, 'store']);

    Route::get('recuperar-contrasena', [PasswordResetLinkController::class, 'create'])
                ->name('contrasena.solicitar');

    Route::post('recuperar-contrasena', [PasswordResetLinkController::class, 'store'])
                ->name('contrasena.correo');

    Route::get('restablecer-contrasena/{token}', [NewPasswordController::class, 'create'])
                ->name('contrasena.restablecer');

    Route::post('restablecer-contrasena', [NewPasswordController::class, 'store'])
                ->name('contrasena.guardar');
});

Route::middleware('auth')->group(function () {
    Route::get('verificar-email', EmailVerificationPromptController::class)
                ->name('verificacion.aviso');

    Route::get('verificar-email/{id}/{hash}', VerifyEmailController::class)
                ->middleware(['signed', 'throttle:6,1'])
                ->name('verificacion.verificar');

    Route::post('email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
                ->middleware('throttle:6,1')
                ->name('verificacion.enviar');

    Route::get('confirmar-contrasena', [ConfirmablePasswordController::class, 'show'])
                ->name('contrasena.confirmar');

    Route::post('confirmar-contrasena', [ConfirmablePasswordController::class, 'store']);

    Route::put('password', [PasswordController::class, 'update'])->name('contrasena.actualizar');

    Route::post('cerrar-sesion', [AuthenticatedSessionController::class, 'destroy'])
                ->name('cerrar-sesion');
});

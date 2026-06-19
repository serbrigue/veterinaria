<?php

use App\Http\Controllers\CitaController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\EspecieController;
use App\Http\Controllers\MascotaController;
use App\Http\Controllers\PanelController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RazaController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('panel')
        : Inertia::render('Publico/Bienvenido', [
            'puedeIniciarSesion' => Route::has('iniciar-sesion'),
            'puedeRegistrarse' => Route::has('registrarse'),
            'laravelVersion' => Application::VERSION,
            'phpVersion' => PHP_VERSION,
        ]);
});

Route::get('/panel', [PanelController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('panel');

Route::middleware('auth')->group(function () {
    Route::get('/perfil', [ProfileController::class, 'editar'])->name('perfil.editar');
    Route::patch('/perfil', [ProfileController::class, 'actualizar'])->name('perfil.actualizar');
    Route::delete('/perfil', [ProfileController::class, 'eliminar'])->name('perfil.eliminar');

    // CRUD COMPLETO (referencia): Mascotas
    Route::get('/mascotas', [MascotaController::class, 'listado'])->name('mascotas.listado');

    // MÓDULO 2 — Especies (manual): Route::get('/especies', ...)->name('especies.listado');

    // MÓDULO 3 — Razas (manual): Route::get('/razas', ...)->name('razas.listado');

    // MÓDULO 4 — Clientes (manual): Route::get('/clientes', ...)->name('clientes.listado');

    // MÓDULO 5 — Citas (manual): Route::get('/citas', ...)->name('citas.listado');

});

require __DIR__.'/auth.php';

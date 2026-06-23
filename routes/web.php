<?php

use App\Http\Controllers\CitaController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\EspecieController;
use App\Http\Controllers\MascotaController;
use App\Http\Controllers\PanelController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RazaController;
use App\Http\Controllers\SucursalController;
use App\Http\Controllers\BoxController;
use App\Http\Controllers\VeterinarioController;
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
    Route::get('/mascotas', [MascotaController::class, 'listado'])
        ->middleware('can:verTodas,App\Models\Mascota')
        ->name('mascotas.listado');
    Route::get('/mascotas/{mascota}', [MascotaController::class, 'detalle'])
        ->middleware('can:ver,mascota')
        ->name('mascotas.detalle');

    // MÓDULO 2 — Especies (manual): Route::get('/especies', ...)->name('especies.listado');
    Route::get('/especies', [EspecieController::class, 'listado'])->middleware('can:verTodas,App\Models\Especie')->name('especies.listado');
    Route::get('/especies/{especie}', [EspecieController::class, 'detalle'])->middleware('can:ver,especie')->name('especies.detalle');

    // MÓDULO 3 — Razas (manual): Route::get('/razas', ...)->name('razas.listado');
    Route::get('/razas', [RazaController::class, 'listado'])->name('razas.listado')->middleware('can:verTodas,App\Models\Raza');
    Route::get('/razas/{raza}', [RazaController::class, 'detalle'])->name('razas.detalle')->middleware('can:ver,raza');

    // MÓDULO 4 — Clientes (manual): Route::get('/clientes', ...)->name('clientes.listado');
    Route::get('/clientes', [ClienteController::class, 'listado'])->name('clientes.listado')->middleware('can:verTodas,App\Models\Cliente');
    Route::get('/clientes/{cliente}', [ClienteController::class, 'detalle'])->name('clientes.detalle')->middleware('can:ver,cliente');

    // MÓDULO 5 — Citas (manual): Route::get('/citas', ...)->name('citas.listado');
    Route::get('/citas', [CitaController::class, 'listado'])->name('citas.listado')->middleware('can:verTodas,App\Models\Cita');
    Route::get('/citas/{cita}', [CitaController::class, 'detalle'])->name('citas.detalle')->middleware('can:ver,cita');

    //Sucursales
    Route::get('/sucursales', [SucursalController::class, 'listado'])->name('sucursales.listado')->middleware('can:verTodas,App\Models\Sucursal');
    Route::get('/sucursales/{sucursal}', [SucursalController::class, 'detalle'])->name('sucursales.detalle')->middleware('can:ver,sucursal');

    //Boxes
    Route::get('/boxes', [BoxController::class, 'listado'])->name('boxes.listado')->middleware('can:verTodas,App\Models\Box');
    Route::get('/boxes/{box}', [BoxController::class, 'detalle'])->name('boxes.detalle')->middleware('can:ver,box');

    //Veterinarios
    Route::get('/veterinarios', [VeterinarioController::class, 'listado'])->name('veterinarios.listado')->middleware('can:verTodas,App\Models\Veterinario');
    Route::get('/veterinarios/{veterinario}', [VeterinarioController::class, 'detalle'])->name('veterinarios.detalle')->middleware('can:ver,veterinario');
});

require __DIR__ . '/auth.php';

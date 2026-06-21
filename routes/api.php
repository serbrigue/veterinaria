<?php

use App\Http\Controllers\AuthApiController;
use App\Http\Controllers\CitaController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\EspecieController;
use App\Http\Controllers\MascotaController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RazaController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/registrarse', [AuthApiController::class, 'registrarse']);
Route::post('/iniciar-sesion', [AuthApiController::class, 'iniciarSesion']);
Route::post('/recuperar-contrasena', [AuthApiController::class, 'recuperarContrasena']);
Route::post('/restablecer-contrasena', [AuthApiController::class, 'restablecerContrasena']);

Route::middleware('auth:sanctum')->get('/usuario', function (Request $solicitud) {
    return $solicitud->user();
});

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/cerrar-sesion', [AuthApiController::class, 'cerrarSesion']);
    Route::post('/confirmar-contrasena', [AuthApiController::class, 'confirmarContrasena']);
    Route::post('/verificacion/enviar', [AuthApiController::class, 'verificacionEnviar']);

    Route::patch('/perfil', [ProfileController::class, 'actualizarApi']);
    Route::put('/perfil/contrasena', [ProfileController::class, 'actualizarContrasenaApi']);
    Route::delete('/perfil', [ProfileController::class, 'eliminarApi']);

    Route::get('/mascotas', [MascotaController::class, 'obtenerTodas'])
        ->middleware('can:verTodas,App\Models\Mascota');
    Route::post('/mascotas', [MascotaController::class, 'crear'])
        ->middleware('can:crear,App\Models\Mascota');
    Route::put('/mascotas/{mascota}', [MascotaController::class, 'actualizar'])
        ->middleware('can:editar,mascota');
    Route::delete('/mascotas/{mascota}', [MascotaController::class, 'eliminar'])
        ->middleware('can:eliminar,mascota');

    // MÓDULO 2 — Especies: GET/POST /especies, PUT/DELETE /especies/{especie}
    Route::get('/especies', [EspecieController::class, 'obtenerTodas'])
        ->middleware('can:verTodas,App\Models\Especie');
    Route::post('/especies', [EspecieController::class, 'crear'])
        ->middleware('can:crear,App\Models\Especie');
    Route::put('/especies/{especie}', [EspecieController::class, 'actualizar'])
        ->middleware('can:editar,especie');
    Route::delete('/especies/{especie}', [EspecieController::class, 'eliminar'])
        ->middleware('can:eliminar,especie');

    // MÓDULO 3 — Razas: GET/POST /razas, PUT/DELETE /razas/{raza}
    Route::get('/razas', [RazaController::class, 'obtenerTodas'])
        ->middleware('can:verTodas,App\Models\Raza');
    Route::post('/razas', [RazaController::class, 'crear'])
        ->middleware('can:crear,App\Models\Raza');
    Route::put('/razas/{raza}', [RazaController::class, 'actualizar'])
        ->middleware('can:editar,raza');
    Route::delete('/razas/{raza}', [RazaController::class, 'eliminar'])
        ->middleware('can:eliminar,raza');

    // MÓDULO 4 — Clientes: GET/POST /clientes, PUT/DELETE /clientes/{cliente}
    Route::get('/clientes', [ClienteController::class, 'obtenerTodas'])
        ->middleware('can:verTodas,App\Models\Cliente');
    Route::post('/clientes', [ClienteController::class, 'crear'])
        ->middleware('can:crear,App\Models\Cliente');
    Route::put('/clientes/{cliente}', [ClienteController::class, 'actualizar'])
        ->middleware('can:editar,cliente');
    Route::delete('/clientes/{cliente}', [ClienteController::class, 'eliminar'])
        ->middleware('can:eliminar,cliente');

    // MÓDULO 5 — Citas: GET/POST /citas, PUT/DELETE /citas/{cita}
    Route::get('/citas', [CitaController::class, 'obtenerTodas'])
        ->middleware('can:verTodas,App\Models\Cita');
    Route::post('/citas', [CitaController::class, 'crear'])
        ->middleware('can:crear,App\Models\Cita');
    Route::put('/citas/{cita}', [CitaController::class, 'actualizar'])
        ->middleware('can:editar,cita');
    Route::delete('/citas/{cita}', [CitaController::class, 'eliminar'])
        ->middleware('can:eliminar,cita');
});

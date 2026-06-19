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

    Route::get('/mascotas', [MascotaController::class, 'obtenerTodas']);
    Route::post('/mascotas', [MascotaController::class, 'crear']);
    Route::put('/mascotas/{mascota}', [MascotaController::class, 'actualizar']);
    Route::delete('/mascotas/{mascota}', [MascotaController::class, 'eliminar']);

    // MÓDULO 2 — Especies: GET/POST /especies, PUT/DELETE /especies/{especie}

    // MÓDULO 3 — Razas: GET/POST /razas, PUT/DELETE /razas/{raza}

    // MÓDULO 4 — Clientes: GET/POST /clientes, PUT/DELETE /clientes/{cliente}

    // MÓDULO 5 — Citas: GET/POST /citas, PUT/DELETE /citas/{cita}
});

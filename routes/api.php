<?php

use App\Http\Controllers\AuthApiController;
use App\Http\Controllers\CitaController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\EspecieController;
use App\Http\Controllers\MascotaController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RazaController;
use App\Http\Controllers\SucursalController;
use App\Http\Controllers\BoxController;
use App\Http\Controllers\VeterinarioController;
use App\Http\Controllers\InsumoController;
use App\Http\Controllers\PrestacionController;
use App\Http\Controllers\CitaCargoController;
use App\Http\Controllers\EquipoMedicoController;
use App\Models\Rol;
use App\Models\User;

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
    Route::post('/clientes/enviar-correo', [ClienteController::class, 'enviarCorreoMasivo']);
    Route::get('/clientes', [ClienteController::class, 'obtenerTodas'])

        ->middleware('can:verTodas,App\Models\Cliente');
    Route::post('/clientes', [ClienteController::class, 'crear'])
        ->middleware('can:crear,App\Models\Cliente');
    Route::put('/clientes/{cliente}', [ClienteController::class, 'actualizar'])
        ->middleware('can:editar,cliente');
    Route::delete('/clientes/{cliente}', [ClienteController::class, 'eliminar'])
        ->middleware('can:eliminar,cliente');

    // MÓDULO 5 — Citas: GET/POST /citas, PUT/PATCH /citas/{cita}
    // ⚠️  horarios-disponibles debe ir ANTES de las rutas con {cita}
    //     para que Laravel no lo trate como un ID de cita
    Route::get('/citas/horarios-disponibles', [CitaController::class, 'horariosDisponibles'])
        ->middleware('can:verTodas,App\Models\Cita');
    Route::get('/citas', [CitaController::class, 'obtenerTodas'])
        ->middleware('can:verTodas,App\Models\Cita');
    Route::post('/citas', [CitaController::class, 'crear'])
        ->middleware('can:crear,App\Models\Cita');
    Route::put('/citas/{cita}', [CitaController::class, 'actualizar'])
        ->middleware('can:editar,cita');
    Route::patch('/citas/{cita}/cancelar', [CitaController::class, 'cancelar'])
        ->middleware('can:cancelar,cita');
    Route::patch('/citas/{cita}/notas', [CitaController::class, 'actualizarNotas'])
        ->middleware('can:editar,cita');
    Route::patch('/citas/{cita}/estado', [CitaController::class, 'actualizarEstado'])
        ->middleware('can:editar,cita');

    // MÓDULO 6 — Cargos de Citas
    Route::post('/citas/{cita}/cargo', [CitaCargoController::class, 'crear'])
        ->middleware('can:crear,App\Models\CitaCargo');
    Route::put('/cargos/{cargo}', [CitaCargoController::class, 'actualizar'])
        ->middleware('can:actualizar,cargo');
    Route::delete('/cargos/{cargo}', [CitaCargoController::class, 'eliminar'])
        ->middleware('can:eliminar,cargo');

    // MÓDULO 7 — Equipo Médico (Cirugías)
    Route::get('/citas/{cita}/equipo',                [EquipoMedicoController::class, 'porCita']);
    Route::post('/citas/{cita}/equipo',               [EquipoMedicoController::class, 'agregar']);
    Route::delete('/citas/{cita}/equipo/{miembro}',   [EquipoMedicoController::class, 'eliminar']);

    //Sucursales
    Route::get('/sucursales', [SucursalController::class, 'obtenerTodas'])
        ->middleware('can:verTodas,App\Models\Sucursal');
    Route::post('/sucursales', [SucursalController::class, 'crear'])
        ->middleware('can:crear,App\Models\Sucursal');
    Route::put('/sucursales/{sucursal}', [SucursalController::class, 'actualizar'])
        ->middleware('can:editar,sucursal');
    Route::delete('/sucursales/{sucursal}', [SucursalController::class, 'eliminar'])
        ->middleware('can:eliminar,sucursal');

    //Boxes
    Route::get('/boxes', [BoxController::class, 'obtenerTodas'])
        ->middleware('can:verTodas,App\Models\Box');
    Route::post('/boxes', [BoxController::class, 'crear'])
        ->middleware('can:crear,App\Models\Box');
    Route::put('/boxes/{box}', [BoxController::class, 'actualizar'])
        ->middleware('can:editar,box');
    Route::delete('/boxes/{box}', [BoxController::class, 'eliminar'])
        ->middleware('can:eliminar,box');

    //Veterinarios
    Route::get('/veterinarios', [VeterinarioController::class, 'obtenerTodas'])
        ->middleware('can:verTodas,App\Models\Veterinario');
    Route::post('/veterinarios', [VeterinarioController::class, 'crear'])
        ->middleware('can:crear,App\Models\Veterinario');
    Route::put('/veterinarios/{veterinario}', [VeterinarioController::class, 'actualizar'])
        ->middleware('can:editar,veterinario');
    Route::delete('/veterinarios/{veterinario}', [VeterinarioController::class, 'eliminar'])
        ->middleware('can:eliminar,veterinario');

    //Insumos
    Route::get('/insumos', [InsumoController::class, 'obtenerTodas'])
        ->middleware('can:verTodas,App\Models\Insumo');
    Route::post('/insumos', [InsumoController::class, 'crear'])
        ->middleware('can:crear,App\Models\Insumo');
    Route::put('/insumos/{insumo}', [InsumoController::class, 'actualizar'])
        ->middleware('can:editar,insumo');
    Route::delete('/insumos/{insumo}', [InsumoController::class, 'eliminar'])
        ->middleware('can:eliminar,insumo');

    //Prestaciones
    Route::get('/prestaciones', [PrestacionController::class, 'obtenerTodas'])
        ->middleware('can:verTodas,App\Models\Prestacion');
    Route::post('/prestaciones', [PrestacionController::class, 'crear'])
        ->middleware('can:crear,App\Models\Prestacion');
    Route::put('/prestaciones/{prestacion}', [PrestacionController::class, 'actualizar'])
        ->middleware('can:editar,prestacion');
    Route::delete('/prestaciones/{prestacion}', [PrestacionController::class, 'eliminar'])
        ->middleware('can:eliminar,prestacion');
});

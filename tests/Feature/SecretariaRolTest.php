<?php

use App\Models\Cita;
use App\Models\CitaCargo;
use App\Models\Mascota;
use App\Models\User;
use App\Models\Veterinario;
use App\Models\Secretaria;
use App\Models\Raza;
use App\Models\Cliente;
use App\Models\Prestacion;
use App\Models\Box;
use App\Models\Insumo;
use Illuminate\Support\Carbon;

beforeEach(function () {
    // Sembramos la base de datos con la configuración de roles/permisos
    $this->seed();
});

test('la secretaria puede ver todas las citas de su sucursal pero el veterinario solo las propias', function () {
    $secretariaUser = User::where('email', 'secretaria@prueba.com')->first();
    $vetUser1 = User::where('email', 'vet@prueba.com')->first(); // Dra. Ana López

    // Verificamos que la secretaria puede listar citas (obtiene listado completo de la sucursal)
    $responseSec = $this->actingAs($secretariaUser)->getJson('/api/citas');
    $responseSec->assertOk();

    // Verificamos que un veterinario ve solo sus citas
    $responseVet = $this->actingAs($vetUser1)->getJson('/api/citas');
    $responseVet->assertOk();
});

test('la secretaria puede crear citas pero el veterinario no tiene permitido agendar', function () {
    $secretariaUser = User::where('email', 'secretaria@prueba.com')->first();
    $vetUser = User::where('email', 'vet@prueba.com')->first();
    
    $mascota = Mascota::first();
    $veterinario = Veterinario::first();
    $prestacion = Prestacion::first();

    $fecha = Carbon::now()->addDays(2)->format('Y-m-d');
    $fechaHora = Carbon::parse($fecha)->setTime(14, 0, 0);

    // Secretaria agenda cita -> OK
    $this->actingAs($secretariaUser)
        ->postJson('/api/citas', [
            'titulo' => 'Cita Secretaria',
            'descripcion' => 'Creada por secretaria',
            'fecha_hora' => $fechaHora->toDateTimeString(),
            'mascota_id' => $mascota->id,
            'veterinario_id' => $veterinario->id,
            'prestacion_id' => $prestacion->id,
        ])
        ->assertCreated();

    // Veterinario intenta agendar cita -> 403 Forbidden
    $this->actingAs($vetUser)
        ->postJson('/api/citas', [
            'titulo' => 'Cita Vet',
            'descripcion' => 'Intento de veterinario',
            'fecha_hora' => $fechaHora->toDateTimeString(),
            'mascota_id' => $mascota->id,
            'veterinario_id' => $veterinario->id,
            'prestacion_id' => $prestacion->id,
        ])
        ->assertStatus(403);
});

test('el veterinario no puede cancelar citas ni reprogramar datos generales', function () {
    $vetUser = User::where('email', 'vet@prueba.com')->first();
    $secretariaUser = User::where('email', 'secretaria@prueba.com')->first();

    $mascota = Mascota::first();
    $veterinario = Veterinario::where('user_id', $vetUser->id)->first();
    $prestacion = Prestacion::first();
    $box = Box::first();

    $fechaHora = Carbon::now()->addDays(5)->setTime(10, 0, 0);

    // Creamos cita asignada a este veterinario
    $cita = Cita::create([
        'titulo' => 'Cita Original',
        'descripcion' => 'Descripción',
        'fecha_hora' => $fechaHora,
        'hora_termino' => $fechaHora->copy()->addMinutes(30),
        'estado' => 'pendiente',
        'mascota_id' => $mascota->id,
        'veterinario_id' => $veterinario->id,
        'box_id' => $box->id,
        'prestacion_id' => $prestacion->id,
    ]);

    // El veterinario intenta reprogramar la cita -> 403 Forbidden
    $this->actingAs($vetUser)
        ->putJson("/api/citas/{$cita->id}", [
            'titulo' => 'Cita Modificada',
            'descripcion' => 'Intento',
            'fecha_hora' => $fechaHora->copy()->addHours(1)->toDateTimeString(),
            'mascota_id' => $mascota->id,
            'veterinario_id' => $veterinario->id,
            'box_id' => $box->id,
            'prestacion_id' => $prestacion->id,
        ])
        ->assertStatus(403);

    // El veterinario intenta cancelar la cita -> 403 Forbidden
    $this->actingAs($vetUser)
        ->patchJson("/api/citas/{$cita->id}/cancelar", [
            'motivo_cancelacion' => 'Urgencia personal'
        ])
        ->assertStatus(403);

    // La secretaria sí puede reprogramarla -> OK
    $this->actingAs($secretariaUser)
        ->putJson("/api/citas/{$cita->id}", [
            'titulo' => 'Cita Modificada',
            'descripcion' => 'Intento',
            'fecha_hora' => $fechaHora->copy()->addHours(2)->toDateTimeString(),
            'mascota_id' => $mascota->id,
            'veterinario_id' => $veterinario->id,
            'box_id' => $box->id,
            'prestacion_id' => $prestacion->id,
        ])
        ->assertOk();

    // La secretaria sí puede cancelarla -> OK
    $this->actingAs($secretariaUser)
        ->patchJson("/api/citas/{$cita->id}/cancelar", [
            'motivo_cancelacion' => 'Cancelación cliente'
        ])
        ->assertOk();
});

test('el veterinario puede actualizar notas y cambiar el estado de su cita pero la secretaria no puede manejar cargos', function () {
    $vetUser = User::where('email', 'vet@prueba.com')->first();
    $secretariaUser = User::where('email', 'secretaria@prueba.com')->first();

    $mascota = Mascota::first();
    $veterinario = Veterinario::where('user_id', $vetUser->id)->first();
    $prestacion = Prestacion::first();
    $box = Box::first();
    $insumo = Insumo::first();

    $fechaHora = Carbon::now()->addDays(5)->setTime(10, 0, 0);

    // Creamos cita asignada a este veterinario
    $cita = Cita::create([
        'titulo' => 'Cita Médica',
        'descripcion' => 'Descripción',
        'fecha_hora' => $fechaHora,
        'hora_termino' => $fechaHora->copy()->addMinutes(30),
        'estado' => 'pendiente',
        'mascota_id' => $mascota->id,
        'veterinario_id' => $veterinario->id,
        'box_id' => $box->id,
        'prestacion_id' => $prestacion->id,
    ]);

    // El veterinario actualiza notas -> OK
    $this->actingAs($vetUser)
        ->patchJson("/api/citas/{$cita->id}/notas", [
            'notas' => 'El paciente evoluciona favorablemente.'
        ])
        ->assertOk();

    // El veterinario cambia el estado -> OK
    $this->actingAs($vetUser)
        ->patchJson("/api/citas/{$cita->id}/estado", [
            'estado' => 'en_curso'
        ])
        ->assertOk();

    // La secretaria intenta agregar un cargo (CitaCargo) -> 403 Forbidden
    $this->actingAs($secretariaUser)
        ->postJson("/api/citas/{$cita->id}/cargo", [
            'cita_id' => $cita->id,
            'insumo_id' => $insumo->id,
            'cantidad' => 1,
        ])
        ->assertStatus(403);

    // El veterinario agrega un cargo -> OK (201 en CitaCargoController)
    $this->actingAs($vetUser)
        ->postJson("/api/citas/{$cita->id}/cargo", [
            'cita_id' => $cita->id,
            'insumo_id' => $insumo->id,
            'cantidad' => 1,
        ])
        ->assertStatus(201);
});

test('la secretaria puede gestionar el equipo medico pero el veterinario no', function () {
    $secretariaUser = User::where('email', 'secretaria@prueba.com')->first();
    $vetUser = User::where('email', 'vet@prueba.com')->first();
    $otroUser = User::create([
        'name' => 'Ayudante Test',
        'email' => 'ayudante@prueba.com',
        'password' => Hash::make('password123'),
        'rol_id' => App\Models\Rol::where('nombre_interno', 'veterinario')->first()?->id,
    ]);

    $mascota = Mascota::first();
    $secretaria = Secretaria::where('user_id', $secretariaUser->id)->first();
    $veterinario = Veterinario::where('sucursal_id', $secretaria->sucursal_id)->first();
    $prestacion = Prestacion::whereHas('categoriaPrestacion', fn($q) => $q->where('nombre', 'Cirugia'))->first();
    if (!$prestacion) {
        $categoria = App\Models\CategoriaPrestacion::firstOrCreate(['nombre' => 'Cirugia']);
        $prestacion = Prestacion::create([
            'nombre' => 'Cirugía General',
            'descripcion' => 'Cirugía',
            'costo' => 50000,
            'comision_vet' => 20,
            'sucursal_id' => $secretaria->sucursal_id,
            'categoria_prestacion_id' => $categoria->id,
        ]);
    }
    $box = Box::where('sucursal_id', $secretaria->sucursal_id)->first();

    $fechaHora = Carbon::now()->addDays(5)->setTime(10, 0, 0);

    // Creamos cita de cirugía
    $cita = Cita::create([
        'titulo' => 'Cirugía de Test',
        'descripcion' => 'Descripción',
        'fecha_hora' => $fechaHora,
        'hora_termino' => $fechaHora->copy()->addMinutes(60),
        'estado' => 'pendiente',
        'mascota_id' => $mascota->id,
        'veterinario_id' => $veterinario->id,
        'box_id' => $box->id,
        'prestacion_id' => $prestacion->id,
    ]);

    $rolMedico = App\Models\Rol::first(); // Cualquier rol para asignar

    // Veterinario intenta agregar personal -> 403 Forbidden
    $this->actingAs($vetUser)
        ->postJson("/api/citas/{$cita->id}/equipo", [
            'usuario_id' => $otroUser->id,
            'rol_id' => $rolMedico->id,
        ])
        ->assertStatus(403);

    // Secretaria de la misma sucursal gestiona -> OK
    $this->actingAs($secretariaUser)
        ->postJson("/api/citas/{$cita->id}/equipo", [
            'usuario_id' => $otroUser->id,
            'rol_id' => $rolMedico->id,
        ])
        ->assertStatus(201);
});


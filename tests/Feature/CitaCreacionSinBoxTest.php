<?php

use App\Models\Cita;
use App\Models\User;
use App\Models\Veterinario;
use App\Models\Box;
use App\Models\Prestacion;
use App\Models\Mascota;
use Illuminate\Support\Carbon;

test('crear cita sin box_id crea la cita con box_id null', function () {
    $this->seed();

    $user = User::where('email', 'admin@prueba.com')->first();
    $veterinario = Veterinario::first();
    $prestacion = Prestacion::first();
    $mascota = Mascota::first();

    $fecha = Carbon::now()->addDays(2)->format('Y-m-d');
    $fechaHora = Carbon::parse($fecha)->setTime(14, 0, 0);

    $response = $this->actingAs($user)
        ->postJson('/api/citas', [
            'titulo' => 'Cita Sin Box de Prueba',
            'descripcion' => 'Creada por cliente sin definir box',
            'fecha_hora' => $fechaHora->toDateTimeString(),
            'mascota_id' => $mascota->id,
            'veterinario_id' => $veterinario->id,
            'prestacion_id' => $prestacion->id,
        ])
        ->assertCreated()
        ->assertJsonPath('box_id', null);

    $citaId = $response->json('id');
    expect(Cita::find($citaId)->box_id)->toBeNull();
});

test('actualizar cita asignando un box ocupado retorna error de solapamiento', function () {
    $this->seed();

    $user = User::where('email', 'admin@prueba.com')->first();
    $veterinario = Veterinario::first();
    $prestacion = Prestacion::first();
    $box = Box::first();
    $mascota = Mascota::first();

    $fecha = Carbon::now()->addDays(3)->format('Y-m-d');
    $fechaHora = Carbon::parse($fecha)->setTime(15, 0, 0);

    // Creamos cita A en el Box 1 de 15:00 a 15:30
    $citaA = Cita::create([
        'titulo' => 'Cita A',
        'descripcion' => 'Ocupa el box',
        'fecha_hora' => $fechaHora,
        'hora_termino' => $fechaHora->copy()->addMinutes(30),
        'estado' => 'pendiente',
        'mascota_id' => $mascota->id,
        'veterinario_id' => $veterinario->id,
        'box_id' => $box->id,
        'prestacion_id' => $prestacion->id,
    ]);

    // Creamos cita B sin box en el mismo horario
    $citaB = Cita::create([
        'titulo' => 'Cita B',
        'descripcion' => 'Creada sin box',
        'fecha_hora' => $fechaHora,
        'hora_termino' => $fechaHora->copy()->addMinutes(30),
        'estado' => 'pendiente',
        'mascota_id' => $mascota->id,
        'veterinario_id' => $veterinario->id,
        'box_id' => null,
        'prestacion_id' => $prestacion->id,
    ]);

    // Intentamos actualizar cita B para asignarle el mismo Box 1
    $this->actingAs($user)
        ->putJson("/api/citas/{$citaB->id}", [
            'titulo' => 'Cita B Modificada',
            'descripcion' => 'Intentando asignar box ocupado',
            'fecha_hora' => $fechaHora->toDateTimeString(),
            'mascota_id' => $mascota->id,
            'veterinario_id' => $veterinario->id,
            'box_id' => $box->id,
            'prestacion_id' => $prestacion->id,
        ])
        ->assertStatus(409)
        ->assertJsonPath('error', 'No se puede actualizar la cita, el box ya está ocupado en ese horario');
});

test('actualizar cita asignando un box compatible y libre tiene exito', function () {
    $this->seed();

    $user = User::where('email', 'admin@prueba.com')->first();
    $veterinario = Veterinario::first();
    $prestacion = Prestacion::first();
    $box = Box::first();
    $mascota = Mascota::first();

    $fecha = Carbon::now()->addDays(4)->format('Y-m-d');
    $fechaHora = Carbon::parse($fecha)->setTime(11, 0, 0);

    // Creamos la cita sin box
    $cita = Cita::create([
        'titulo' => 'Cita de Control',
        'descripcion' => 'Control rutinario',
        'fecha_hora' => $fechaHora,
        'hora_termino' => $fechaHora->copy()->addMinutes(30),
        'estado' => 'pendiente',
        'mascota_id' => $mascota->id,
        'veterinario_id' => $veterinario->id,
        'box_id' => null,
        'prestacion_id' => $prestacion->id,
    ]);

    // Asignamos el box 1 vía API
    $this->actingAs($user)
        ->putJson("/api/citas/{$cita->id}", [
            'titulo' => 'Cita de Control',
            'descripcion' => 'Control rutinario',
            'fecha_hora' => $fechaHora->toDateTimeString(),
            'mascota_id' => $mascota->id,
            'veterinario_id' => $veterinario->id,
            'box_id' => $box->id,
            'prestacion_id' => $prestacion->id,
        ])
        ->assertOk()
        ->assertJsonPath('box_id', $box->id);

    expect(Cita::find($cita->id)->box_id)->toBe($box->id);
});

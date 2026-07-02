<?php

use App\Models\Cita;
use App\Models\User;
use App\Models\Veterinario;
use Illuminate\Support\Carbon;

test('horarios disponibles api returns correct slots and marks occupied slots correctly based on veterinarian availability', function () {
    $this->seed();

    $user = User::where('email', 'admin@prueba.com')->first();
    $veterinario = Veterinario::first();

    // Create an appointment for the veterinarian on a specific date and time
    // E.g. Tomorrow at 10:00 AM (which is in the 'normal' slot range 9:00 - 18:00)
    $fecha = Carbon::now()->addDays(5)->format('Y-m-d');
    $fechaHora = Carbon::parse($fecha)->setTime(10, 0, 0);
    $horaTermino = Carbon::parse($fecha)->setTime(10, 30, 0);

    Cita::create([
        'titulo' => 'Cita Ocupada',
        'descripcion' => 'Solapamiento de prueba',
        'fecha_hora' => $fechaHora,
        'hora_termino' => $horaTermino,
        'estado' => 'pendiente',
        'mascota_id' => 1,
        'veterinario_id' => $veterinario->id,
        'box_id' => 1,
        'prestacion_id' => 1,
        'tipo' => 'normal',
    ]);

    $response = $this->actingAs($user)
        ->getJson("/api/citas/horarios-disponibles?fecha={$fecha}&veterinario_id={$veterinario->id}")
        ->assertOk()
        ->assertJsonStructure([
            'normal',
            'urgencia'
        ]);

    $normalSlots = $response->json('normal');
    
    // Find the slot at 10:00
    $slot10 = null;
    foreach ($normalSlots as $slot) {
        if ($slot['hora'] === '10:00') {
            $slot10 = $slot;
            break;
        }
    }

    expect($slot10)->not->toBeNull();
    expect($slot10['disponible'])->toBeFalse();

    // Find the slot at 10:30
    $slot1030 = null;
    foreach ($normalSlots as $slot) {
        if ($slot['hora'] === '10:30') {
            $slot1030 = $slot;
            break;
        }
    }

    expect($slot1030)->not->toBeNull();
    expect($slot1030['disponible'])->toBeTrue();
});

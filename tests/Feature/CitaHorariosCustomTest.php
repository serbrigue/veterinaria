<?php

use App\Models\User;
use App\Models\Veterinario;
use Illuminate\Support\Carbon;

test('actualizar horario de veterinario guarda los datos en base de datos', function () {
    $this->seed();

    $veterinario = Veterinario::first();
    $user = $veterinario->usuario;

    $horarioData = [
        [
            'dia' => 1, // Lunes
            'normal' => ['activo' => true, 'inicio' => '09:00', 'fin' => '13:00'],
            'urgencia' => ['activo' => false, 'inicio' => '', 'fin' => ''],
        ],
        [
            'dia' => 3, // Miercoles
            'normal' => ['activo' => false, 'inicio' => '', 'fin' => ''],
            'urgencia' => ['activo' => false, 'inicio' => '', 'fin' => ''],
        ],
    ];

    $this->actingAs($user)
        ->patchJson("/api/veterinarios/{$veterinario->id}/horario", [
            'horario' => $horarioData,
        ])
        ->assertOk()
        ->assertJsonPath('mensaje', 'Horario actualizado correctamente.');

    $updatedVet = Veterinario::find($veterinario->id);
    expect($updatedVet->horario)->toBeArray();
    expect($updatedVet->horario[0]['normal']['fin'])->toBe('13:00');
});

test('horarios disponibles retorna slots basados en el horario personalizado del veterinario', function () {
    $this->seed();

    $veterinario = Veterinario::first();
    $user = User::where('email', 'admin@prueba.com')->first();

    // Seteamos un horario customizado:
    // Lunes (1): Sólo de 09:00 a 11:00
    // Miércoles (3): No trabaja
    $horarioData = [
        [
            'dia' => 1, // Lunes
            'normal' => ['activo' => true, 'inicio' => '09:00', 'fin' => '11:00'],
            'urgencia' => ['activo' => false, 'inicio' => '', 'fin' => ''],
        ],
        [
            'dia' => 3, // Miercoles
            'normal' => ['activo' => false, 'inicio' => '', 'fin' => ''],
            'urgencia' => ['activo' => false, 'inicio' => '', 'fin' => ''],
        ],
    ];

    $veterinario->update(['horario' => $horarioData]);

    // Buscamos un Lunes futuro
    $lunes = Carbon::now()->next(Carbon::MONDAY)->format('Y-m-d');

    $responseLunes = $this->actingAs($user)
        ->getJson("/api/citas/horarios-disponibles?fecha={$lunes}&veterinario_id={$veterinario->id}")
        ->assertOk();

    $normalLunes = $responseLunes->json('normal');
    $urgenciaLunes = $responseLunes->json('urgencia');

    // Debieran haber 4 slots de 30 mins: 09:00, 09:30, 10:00, 10:30 (ya que fin es 11:00)
    expect($normalLunes)->toHaveCount(4);
    expect($urgenciaLunes)->toBeEmpty();

    // Buscamos un Miércoles futuro
    $miercoles = Carbon::now()->next(Carbon::WEDNESDAY)->format('Y-m-d');

    $responseMiercoles = $this->actingAs($user)
        ->getJson("/api/citas/horarios-disponibles?fecha={$miercoles}&veterinario_id={$veterinario->id}")
        ->assertOk();

    expect($responseMiercoles->json('normal'))->toBeEmpty();
    expect($responseMiercoles->json('urgencia'))->toBeEmpty();
});

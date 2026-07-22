<?php

use App\Models\BloqueoHorario;
use App\Models\Veterinario;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

it('detecta correctamente si un bloqueo de horario solapa con una fecha y hora dadas', function () {
    // Arrange
    $user = User::factory()->create();
    $veterinario = Veterinario::create([
        'user_id' => $user->id,
        'rut' => '12345678-9',
        'especialidad_id' => 1,
        'sucursal_id' => 1,
    ]);
    
    // Creamos un bloqueo el día de hoy de 10:00 a 12:00
    $bloqueo = BloqueoHorario::create([
        'veterinario_id' => $veterinario->id,
        'fecha_inicio' => Carbon::today(),
        'fecha_fin' => Carbon::today(),
        'hora_inicio' => '10:00:00',
        'hora_fin' => '12:00:00',
        'motivo' => 'Reunión'
    ]);

    // Act & Assert
    
    // 1. Antes del bloqueo (9:00 - 9:30) -> No solapa
    $solapaAntes = BloqueoHorario::where('veterinario_id', $veterinario->id)
        ->where('fecha_inicio', '<=', Carbon::today())
        ->where('fecha_fin', '>=', Carbon::today())
        ->where('hora_inicio', '<', '09:30:00')
        ->where('hora_fin', '>', '09:00:00')
        ->exists();
    expect($solapaAntes)->toBeFalse();

    // 2. Exactamente en el bloqueo (10:30 - 11:00) -> Solapa
    $solapaDentro = BloqueoHorario::where('veterinario_id', $veterinario->id)
        ->where('fecha_inicio', '<=', Carbon::today())
        ->where('fecha_fin', '>=', Carbon::today())
        ->where('hora_inicio', '<', '11:00:00')
        ->where('hora_fin', '>', '10:30:00')
        ->exists();
    expect($solapaDentro)->toBeTrue();
});

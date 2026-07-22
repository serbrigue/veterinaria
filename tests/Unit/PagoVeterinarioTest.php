<?php

use App\Models\PagoVeterinario;
use App\Models\Transaccion;
use App\Models\Veterinario;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

it('calcula correctamente el monto total al generar un pago veterinario', function () {
    // Arrange
    $user = User::factory()->create();
    $veterinario = Veterinario::create([
        'user_id' => $user->id,
        'rut' => '12345678-9',
        'especialidad_id' => 1,
        'sucursal_id' => 1,
    ]);

    // Creamos una prestación con un % de comisión para el veterinario
    $prestacion = \App\Models\Prestacion::create([
        'sucursal_id' => 1,
        'nombre' => 'Consulta Veterinaria',
        'descripcion' => 'Consulta general',
        'precio_base' => 10000,
        'comision_vet' => 60, // 60%
    ]);

    // Creamos 2 citas para asociar al veterinario
    $cita1 = \App\Models\Cita::create([
        'titulo' => 'Cita Test 1',
        'veterinario_id' => $veterinario->id,
        'prestacion_id' => $prestacion->id,
        'fecha_hora' => Carbon::now()->subDays(2)->setTime(10, 0),
        'hora_termino' => Carbon::now()->subDays(2)->setTime(10, 30),
        'estado' => 'completada'
    ]);

    $cita2 = \App\Models\Cita::create([
        'titulo' => 'Cita Test 2',
        'veterinario_id' => $veterinario->id,
        'prestacion_id' => $prestacion->id,
        'fecha_hora' => Carbon::now()->subDays(1)->setTime(11, 0),
        'hora_termino' => Carbon::now()->subDays(1)->setTime(11, 30),
        'estado' => 'completada'
    ]);

    // Creamos 2 transacciones pagadas asociadas a las citas
    Transaccion::create([
        'cita_id' => $cita1->id,
        'cliente_id' => 1,
        'monto_total' => 10000,
        'estado' => 'pagado',
        'fecha_transaccion' => Carbon::now()->subDays(2)
    ]);

    Transaccion::create([
        'cita_id' => $cita2->id,
        'cliente_id' => 1,
        'monto_total' => 15000,
        'monto_pagado' => 15000,
        'estado' => 'pagado',
        'fecha_transaccion' => Carbon::now()->subDays(1)
    ]);

    // Act
    // Recuperamos las citas pagadas del veterinario junto a la prestación y transacción
    $citasPagadas = \App\Models\Cita::with(['transaccion', 'prestacion'])
        ->where('veterinario_id', $veterinario->id)
        ->whereHas('transaccion', function ($query) {
            $query->where('estado', 'pagado');
        })->get();

    $totalGenerado = 0;
    $comision = 0;

    foreach ($citasPagadas as $cita) {
        $monto = $cita->transaccion->monto_total;
        $totalGenerado += $monto;
        
        // Calcular la comisión basada en el % asignado a la prestación
        $porcentaje = $cita->prestacion ? ($cita->prestacion->comision_vet / 100) : 0;
        $comision += $monto * $porcentaje;
    }

    $pago = PagoVeterinario::create([
        'veterinario_id' => $veterinario->id,
        'monto_total' => $comision,
        'estado' => 'pendiente',
        'periodo_inicio' => Carbon::now()->startOfMonth(),
        'periodo_fin' => Carbon::now()->endOfMonth()
    ]);

    // Assert
    expect($totalGenerado)->toEqual(25000);
    expect((int)$pago->monto_total)->toEqual(15000); // 25000 * 0.60
});

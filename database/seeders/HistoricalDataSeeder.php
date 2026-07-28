<?php

namespace Database\Seeders;

use App\Models\Box;
use App\Models\CategoriaPrestacion;
use App\Models\Cita;
use App\Models\EquipoMedico;
use App\Models\Mascota;
use App\Models\Prestacion;
use App\Models\Rol;
use App\Models\Transaccion;
use App\Models\User;
use App\Models\Veterinario;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class HistoricalDataSeeder extends Seeder
{
    public function run(): void
    {
        $mascotas = Mascota::with('cliente.usuario')->get();
        $veterinarios = Veterinario::with('usuario')->get();
        $boxes = Box::all();
        $prestaciones = Prestacion::all();
        $insumos = \App\Models\Insumo::where('estado', 'activo')->get();

        if ($mascotas->isEmpty() || $veterinarios->isEmpty() || $boxes->isEmpty() || $prestaciones->isEmpty()) {
            $this->command->error('Se requieren Mascotas, Veterinarios, Boxes y Prestaciones previas para generar historial.');

            return;
        }

        // Obtener categoría de Cirugía y roles del personal médico de apoyo
        $categoriaCirugia = CategoriaPrestacion::where('nombre', 'Cirugía')->first();
        $categoriaCirugiaId = $categoriaCirugia ? $categoriaCirugia->id : null;

        $rolArsenalero = Rol::where('nombre_interno', 'arsenalero')->first();
        $rolAnestesista = Rol::where('nombre_interno', 'anestesista')->first();
        $rolTens = Rol::where('nombre_interno', 'tens')->first();

        $arsenaleros = $rolArsenalero ? User::where('rol_id', $rolArsenalero->id)->get() : collect();
        $anestesistas = $rolAnestesista ? User::where('rol_id', $rolAnestesista->id)->get() : collect();
        $tens = $rolTens ? User::where('rol_id', $rolTens->id)->get() : collect();

        $diasAtras = 60; // 2 meses de datos históricos
        $totalCitasGeneradas = 0;
        $totalEquiposAsignados = 0;

        $estadosPosibles = ['completada', 'completada', 'completada', 'cancelada']; // 75% completadas, 25% canceladas
        $metodosPago = ['tarjeta', 'efectivo', 'transferencia'];

        for ($i = $diasAtras; $i >= 1; $i--) {
            $fechaDia = Carbon::now()->subDays($i);

            // Generar un número aleatorio de citas por día (entre 2 y 6)
            $citasDia = rand(2, 6);

            for ($j = 0; $j < $citasDia; $j++) {
                $veterinario = $veterinarios->random();

                // Encontrar un box que corresponda a la sucursal del veterinario
                $box = $boxes->where('sucursal_id', $veterinario->sucursal_id)->random();
                if (! $box) {
                    continue;
                }

                // Encontrar una prestación de la misma sucursal
                $prestacion = $prestaciones->where('sucursal_id', $veterinario->sucursal_id)->random();
                if (! $prestacion) {
                    continue;
                }

                $mascota = $mascotas->random();
                $estado = $estadosPosibles[array_rand($estadosPosibles)];

                $horaInicio = clone $fechaDia;
                $horaInicio->setTime(rand(9, 17), rand(0, 1) === 0 ? 0 : 30, 0); // Horas entre 9 AM y 5:30 PM
                $horaTermino = clone $horaInicio;
                $horaTermino->addMinutes(30);

                // Crear cita
                $cita = Cita::create([
                    'titulo' => 'Consulta '.$prestacion->nombre,
                    'descripcion' => 'Atención rutinaria generada automáticamente.',
                    'fecha_hora' => $horaInicio,
                    'hora_termino' => $horaTermino,
                    'estado' => $estado,
                    'mascota_id' => $mascota->id,
                    'veterinario_id' => $veterinario->id,
                    'box_id' => $box->id,
                    'prestacion_id' => $prestacion->id,
                    'tipo' => rand(1, 10) > 8 ? 'urgencia' : 'normal',
                ]);

                // Si se completó, generar el pago asociado, ficha clínica, receta e insumos
                if ($estado === 'completada') {
                    // Si el cliente es uno de los morosos o por probabilidad del 15% queda pendiente
                    $esMoroso = str_contains($mascota->cliente->usuario->email ?? '', 'deuda') || (rand(1, 100) <= 15);

                    // --- GENERAR FICHA CLÍNICA ---
                    $ficha = \App\Models\FichaClinica::create([
                        'cita_id' => $cita->id,
                        'mascota_id' => $mascota->id,
                        'veterinario_id' => $veterinario->id,
                        'peso_actual' => rand(30, 250) / 10,
                        'frecuencia_cardiaca' => rand(80, 160),
                        'temperatura' => rand(370, 395) / 10,
                        'anamnesis' => 'Paciente asiste a consulta programada.',
                        'sintomas' => rand(1, 10) > 7 ? 'Decaimiento leve.' : 'Ninguno aparente.',
                        'diagnostico' => 'Diagnóstico presuntivo de rutina.',
                    ]);

                    // --- ASIGNAR INSUMOS (1 a 3 insumos al azar) ---
                    $insumosDisponibles = $insumos->where('sucursal_id', $veterinario->sucursal_id);
                    $montoInsumos = 0;
                    if ($insumosDisponibles->count() > 0) {
                        $cantidadInsumosUsados = rand(1, 3);
                        $insumosUsados = $insumosDisponibles->random(min($cantidadInsumosUsados, $insumosDisponibles->count()));
                        
                        $medicamentosReceta = [];

                        foreach ($insumosUsados as $ins) {
                            $cant = rand(1, 2);
                            $subtotal = $ins->precio_venta * $cant;
                            $montoInsumos += $subtotal;

                            // Registrar Cargo
                            \App\Models\CitaCargo::create([
                                'cita_id' => $cita->id,
                                'prestacion_id' => $prestacion->id,
                                'insumo_id' => $ins->id,
                                'cantidad' => $cant,
                                'precio_unitario' => $ins->precio_venta,
                                'subtotal' => $subtotal,
                                'pago_vet' => 0,
                            ]);

                            // Trazabilidad: Movimiento de Inventario (Salida)
                            \App\Models\MovimientoInventario::create([
                                'insumo_id' => $ins->id,
                                'tipo' => 'salida',
                                'cantidad' => $cant,
                                'motivo' => "Uso clínico en Cita #{$cita->id}",
                                'usuario_id' => $veterinario->user_id,
                                'cita_id' => $cita->id,
                            ]);

                            // Si es vacuna, registrar en AplicacionVacuna
                            if (stripos($ins->nombre, 'Vacuna') !== false) {
                                \App\Models\AplicacionVacuna::create([
                                    'cita_id' => $cita->id,
                                    'mascota_id' => $mascota->id,
                                    'nombre_vacuna' => $ins->nombre,
                                    'fecha_aplicacion' => $horaInicio,
                                    'fecha_proxima_dosis' => Carbon::parse($horaInicio)->addYear(),
                                    'numero_lote' => 'LOTE-' . rand(1000, 9999),
                                    'notas' => 'Vacunación rutinaria histórica.',
                                ]);
                            }

                            // Si es medicamento, añadir a la receta (60% probabilidad)
                            if ($ins->categoria_insumo_id !== null && rand(1, 100) <= 60 && stripos($ins->nombre, 'Vacuna') === false && stripos($ins->nombre, 'Jeringa') === false) {
                                $medicamentosReceta[] = [
                                    'nombre' => $ins->nombre,
                                    'dosis' => rand(1, 2) . ' comprimidos',
                                    'frecuencia' => 'cada 12 horas',
                                    'duracion' => rand(3, 7) . ' días',
                                    'insumo_id' => $ins->id
                                ];
                            }
                        }

                        // Crear Receta si hay medicamentos
                        if (count($medicamentosReceta) > 0) {
                            \App\Models\RecetaMedica::create([
                                'ficha_clinica_id' => $ficha->id,
                                'medicamentos' => $medicamentosReceta,
                                'indicaciones_generales' => 'Administrar junto con la comida. Reposo relativo.',
                                'comprado_en_clinica' => true,
                            ]);
                        }
                    }

                    $montoTotal = $prestacion->precio_base + $montoInsumos;

                    Transaccion::create([
                        'cita_id' => $cita->id,
                        'cliente_id' => $mascota->cliente_id,
                        'monto_total' => $montoTotal,
                        'monto_pagado' => $esMoroso ? 0.00 : $montoTotal,
                        'estado' => $esMoroso ? 'pendiente' : 'pagado',
                        'metodo_pago' => $esMoroso ? null : $metodosPago[array_rand($metodosPago)],
                        'fecha_pago' => $esMoroso ? null : clone $horaTermino,
                    ]);
                }

                // Asignar Equipo Médico si la cita es una Cirugía
                if ($categoriaCirugiaId && $prestacion->categoria_prestacion_id == $categoriaCirugiaId && $estado !== 'cancelada') {
                    // 1. Arsenalero (Obligatorio)
                    $arsenaleroAsignado = null;
                    foreach ($arsenaleros as $c) {
                        $overlap = EquipoMedico::where('usuario_id', $c->id)
                            ->whereHas('cita', function ($q) use ($horaInicio, $horaTermino) {
                                $q->where('estado', '!=', 'cancelada')
                                    ->where('fecha_hora', '<', $horaTermino)
                                    ->where('hora_termino', '>', $horaInicio);
                            })
                            ->exists();
                        if (! $overlap) {
                            $arsenaleroAsignado = $c;
                            break;
                        }
                    }
                    if (! $arsenaleroAsignado && $arsenaleros->isNotEmpty()) {
                        $arsenaleroAsignado = $arsenaleros->random();
                    }

                    if ($arsenaleroAsignado && $rolArsenalero) {
                        EquipoMedico::create([
                            'cita_id' => $cita->id,
                            'usuario_id' => $arsenaleroAsignado->id,
                            'rol_id' => $rolArsenalero->id,
                        ]);
                        $totalEquiposAsignados++;
                    }

                    // 2. Anestesista (Opcional, 65% de probabilidad)
                    if (rand(1, 100) <= 65) {
                        $anestesistaAsignado = null;
                        foreach ($anestesistas as $c) {
                            $overlap = EquipoMedico::where('usuario_id', $c->id)
                                ->whereHas('cita', function ($q) use ($horaInicio, $horaTermino) {
                                    $q->where('estado', '!=', 'cancelada')
                                        ->where('fecha_hora', '<', $horaTermino)
                                        ->where('hora_termino', '>', $horaInicio);
                                })
                                ->exists();
                            if (! $overlap) {
                                $anestesistaAsignado = $c;
                                break;
                            }
                        }
                        if ($anestesistaAsignado && $rolAnestesista) {
                            EquipoMedico::create([
                                'cita_id' => $cita->id,
                                'usuario_id' => $anestesistaAsignado->id,
                                'rol_id' => $rolAnestesista->id,
                            ]);
                            $totalEquiposAsignados++;
                        }
                    }

                    // 3. TENS (Opcional, 40% de probabilidad)
                    if (rand(1, 100) <= 40) {
                        $tensAsignado = null;
                        foreach ($tens as $c) {
                            $overlap = EquipoMedico::where('usuario_id', $c->id)
                                ->whereHas('cita', function ($q) use ($horaInicio, $horaTermino) {
                                    $q->where('estado', '!=', 'cancelada')
                                        ->where('fecha_hora', '<', $horaTermino)
                                        ->where('hora_termino', '>', $horaInicio);
                                })
                                ->exists();
                            if (! $overlap) {
                                $tensAsignado = $c;
                                break;
                            }
                        }
                        if ($tensAsignado && $rolTens) {
                            EquipoMedico::create([
                                'cita_id' => $cita->id,
                                'usuario_id' => $tensAsignado->id,
                                'rol_id' => $rolTens->id,
                            ]);
                            $totalEquiposAsignados++;
                        }
                    }
                }

                $totalCitasGeneradas++;
            }
        }

        $this->command->info("¡Seeder Histórico ejecutado con éxito! Se generaron {$totalCitasGeneradas} citas y transacciones en los últimos {$diasAtras} días. Se asignaron {$totalEquiposAsignados} miembros a equipos médicos de cirugía.");
    }
}

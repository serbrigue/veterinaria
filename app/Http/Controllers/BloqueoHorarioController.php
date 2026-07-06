<?php

namespace App\Http\Controllers;

use App\Http\Requests\GuardarBloqueoHorarioRequest;
use App\Models\BloqueoHorario;
use App\Models\Cita;
use App\Models\Veterinario;
use Illuminate\Support\Facades\DB;

class BloqueoHorarioController extends Controller
{
    public function crear(GuardarBloqueoHorarioRequest $request, Veterinario $veterinario)
    {
        // Validamos los datos de entrada
        $request->validated();

        // Validamos que la hora de inicio y fin sean correctas
        if (($request->hora_inicio && ! $request->hora_fin) || (! $request->hora_inicio && $request->hora_fin)) {
            return response()->json([
                'message' => 'Debe ingresar tanto la hora de inicio como la de fin, o dejar ambas vacías para bloquear el día completo.',
            ], 422);
        }

        // Validamos que la hora de inicio sea menor a la hora de fin
        if ($request->hora_inicio && $request->hora_fin && $request->hora_inicio >= $request->hora_fin) {
            return response()->json([
                'message' => 'La hora de fin debe ser posterior a la hora de inicio.',
            ], 422);
        }

        // Validamos que no exista un bloqueo en el mismo horario
        $bloqueoExistente = BloqueoHorario::where('veterinario_id', $veterinario->id)
            ->where('fecha_inicio', $request->fecha_inicio)
            ->where('hora_inicio', $request->hora_inicio)
            ->first();

        if ($bloqueoExistente) {
            return response()->json([
                'message' => 'Ya existe un bloqueo en el mismo horario.',
            ], 422);
        }

        // Creamos el bloqueo y cancelamos citas en cascada
        $bloqueo = DB::transaction(function () use ($request, $veterinario) {
            $nuevoBloqueo = BloqueoHorario::create([
                'veterinario_id' => $veterinario->id,
                'fecha_inicio' => $request->fecha_inicio,
                'fecha_fin' => $request->fecha_fin,
                'hora_inicio' => $request->hora_inicio,
                'hora_fin' => $request->hora_fin,
                'motivo' => $request->motivo,
            ]);

            // Buscar citas que se superpongan y cancelarlas
            $queryCitas = Cita::where('veterinario_id', $veterinario->id)
                ->where('estado', 'pendiente')
                ->whereDate('fecha_hora', '>=', $request->fecha_inicio);

            if ($request->fecha_fin) {
                $queryCitas->whereDate('fecha_hora', '<=', $request->fecha_fin);
            } else {
                $queryCitas->whereDate('fecha_hora', '<=', $request->fecha_inicio);
            }

            if ($request->hora_inicio && $request->hora_fin) {
                $queryCitas->where(function ($q) use ($request) {
                    $q->whereTime('fecha_hora', '<', $request->hora_fin)
                        ->whereTime('hora_termino', '>', $request->hora_inicio);
                });
            }

            $queryCitas->update([
                'estado' => 'cancelada',
                'notas' => 'Cita cancelada automáticamente por bloqueo de horario: '.$request->motivo,
            ]);

            return $nuevoBloqueo;
        });

        // Retornamos el bloqueo
        return response()->json([
            'mensaje' => 'Bloqueo registrado correctamente.',
            'bloqueo' => $bloqueo,
        ], 201);
    }

    public function eliminar(BloqueoHorario $bloqueo)
    {
        // Eliminamos el bloqueo
        $bloqueo->delete();

        // Retornamos el bloqueo
        return response()->json(['mensaje' => 'Bloqueo de horario eliminado correctamente.']);
    }
}

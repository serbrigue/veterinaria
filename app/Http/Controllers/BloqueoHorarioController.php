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
        $request->validated();

        $error = $this->validarConflictos($request, $veterinario);
        if ($error) {
            return response()->json(['message' => $error], 422);
        }

        $bloqueo = $this->registrarBloqueo($request, $veterinario);

        return response()->json([
            'mensaje' => 'Bloqueo registrado correctamente.',
            'bloqueo' => $bloqueo,
        ], 201);
    }

    private function validarConflictos($request, Veterinario $veterinario): ?string
    {
        if (($request->hora_inicio && ! $request->hora_fin) || (! $request->hora_inicio && $request->hora_fin)) {
            return 'Debe ingresar tanto la hora de inicio como la de fin, o dejar ambas vacías para bloquear el día completo.';
        }

        if ($request->hora_inicio && $request->hora_fin && $request->hora_inicio >= $request->hora_fin) {
            return 'La hora de fin debe ser posterior a la hora de inicio.';
        }

        $bloqueoExistente = BloqueoHorario::where('veterinario_id', $veterinario->id)
            ->where('fecha_inicio', $request->fecha_inicio)
            ->where('hora_inicio', $request->hora_inicio)
            ->where('especialidad_id', $request->especialidad_id)
            ->where('sucursal_id', $request->sucursal_id)
            ->first();

        if ($bloqueoExistente) {
            return 'Ya existe un bloqueo en el mismo horario.';
        }

        return null;
    }

    private function registrarBloqueo($request, Veterinario $veterinario): BloqueoHorario
    {
        return DB::transaction(function () use ($request, $veterinario) {
            $nuevoBloqueo = BloqueoHorario::create([
                'veterinario_id' => $veterinario->id,
                'fecha_inicio' => $request->fecha_inicio,
                'fecha_fin' => $request->fecha_fin,
                'hora_inicio' => $request->hora_inicio,
                'hora_fin' => $request->hora_fin,
                'especialidad_id' => $request->especialidad_id ?: null,
                'sucursal_id' => $request->sucursal_id ?: null,
                'motivo' => $request->motivo,
            ]);

            $this->cancelarCitasSuperpuestas($request, $veterinario);

            return $nuevoBloqueo;
        });
    }

    private function cancelarCitasSuperpuestas($request, Veterinario $veterinario): void
    {
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

        if ($request->especialidad_id) {
            $queryCitas->whereHas('prestacion', function ($q) use ($request) {
                $q->where('especialidad_id', $request->especialidad_id);
            });
        }

        if ($request->sucursal_id) {
            $queryCitas->where(function ($q) use ($request) {
                $q->whereHas('box', function ($b) use ($request) {
                    $b->where('sucursal_id', $request->sucursal_id);
                })->orWhereHas('prestacion', function ($p) use ($request) {
                    $p->where('sucursal_id', $request->sucursal_id);
                });
            });
        }

        $queryCitas->update([
            'estado' => 'cancelada',
            'notas' => 'Cita cancelada automáticamente por bloqueo de horario: ' . $request->motivo,
        ]);
    }


    public function eliminar(BloqueoHorario $bloqueo)
    {
        // Eliminamos el bloqueo
        $bloqueo->delete();

        // Retornamos el bloqueo
        return response()->json(['mensaje' => 'Bloqueo de horario eliminado correctamente.']);
    }
}

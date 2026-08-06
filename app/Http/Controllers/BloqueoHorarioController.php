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
        // Validación de datos
        $request->validated();

        // Validación de conflictos
        $error = $this->validarConflictos($request, $veterinario);
        if ($error) {
            return response()->json(['message' => $error], 422);
        }

        // Registro del bloqueo
        $bloqueo = $this->registrarBloqueo($request, $veterinario);

        // Retornamos el bloqueo
        return response()->json([
            'mensaje' => 'Bloqueo registrado correctamente.',
            'bloqueo' => $bloqueo,
        ], 201);
    }


    // Validación de conflictos 
    private function validarConflictos($request, Veterinario $veterinario): ?string
    {
        // Si la hora de inicio esta llena pero la de fin no, o viceversa, entonces retorna un mensaje de error
        if (($request->hora_inicio && ! $request->hora_fin) || (! $request->hora_inicio && $request->hora_fin)) {
            return 'Debe ingresar tanto la hora de inicio como la de fin, o dejar ambas vacías para bloquear el día completo.';
        }

        // Si la hora de inicio y la hora de fin estan llenas, entonces la hora de fin debe ser mayor a la hora de inicio
        if ($request->hora_inicio && $request->hora_fin && $request->hora_inicio >= $request->hora_fin) {
            return 'La hora de fin debe ser posterior a la hora de inicio.';
        }

        // Si existe un bloqueo en el mismo horario, entonces retorna un mensaje de error
        $bloqueoExistente = BloqueoHorario::where('veterinario_id', $veterinario->id)
            ->where('fecha_inicio', $request->fecha_inicio)
            ->where('hora_inicio', $request->hora_inicio)
            ->where('especialidad_id', $request->especialidad_id)
            ->where('sucursal_id', $request->sucursal_id)
            ->first();

        // Retornamos un mensaje de error si existe un bloqueo en el mismo horario
        if ($bloqueoExistente) {
            return 'Ya existe un bloqueo en el mismo horario.';
        }

        // Si no hay conflictos, retorna null
        return null;
    }


    private function registrarBloqueo($request, Veterinario $veterinario): BloqueoHorario
    {
        //Registra el bloqueo en la base de datos, si ocurre un error, retorna un mensaje de error
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
        //Busca las citas que se superponen con el bloqueo
        $queryCitas = Cita::where('veterinario_id', $veterinario->id)
            ->where('estado', 'pendiente')
            ->whereDate('fecha_hora', '>=', $request->fecha_inicio);

        //Si existe una fecha de fin, entonces la fecha de fin debe ser mayor a la fecha de inicio
        if ($request->fecha_fin) {
            $queryCitas->whereDate('fecha_hora', '<=', $request->fecha_fin);
        } else {
            $queryCitas->whereDate('fecha_hora', '<=', $request->fecha_inicio);
        }

        //Si existe una hora de inicio y una hora de fin, entonces la hora de fin debe ser mayor a la hora de inicio
        if ($request->hora_inicio && $request->hora_fin) {
            $queryCitas->where(function ($q) use ($request) {
                $q->whereTime('fecha_hora', '<', $request->hora_fin)
                    ->whereTime('hora_termino', '>', $request->hora_inicio);
            });
        }

        //Si existe una especialidad, entonces la especialidad debe ser la misma
        if ($request->especialidad_id) {
            $queryCitas->whereHas('prestacion', function ($q) use ($request) {
                $q->where('especialidad_id', $request->especialidad_id);
            });
        }

        //Si existe una sucursal, entonces la sucursal debe ser la misma
        if ($request->sucursal_id) {
            $queryCitas->where(function ($q) use ($request) {
                $q->whereHas('box', function ($b) use ($request) {
                    $b->where('sucursal_id', $request->sucursal_id);
                })->orWhereHas('prestacion', function ($p) use ($request) {
                    $p->where('sucursal_id', $request->sucursal_id);
                });
            });
        }

        //Actualiza el estado de las citas a cancelada y agrega una nota
        $queryCitas->update([
            'estado' => 'cancelada',
            'notas' => 'Cita cancelada automáticamente por bloqueo de horario: ' . $request->motivo,
        ]);
    }


    //Elimina el bloqueo de horario
    public function eliminar(BloqueoHorario $bloqueo)
    {
        // Eliminamos el bloqueo
        $bloqueo->delete();

        // Retornamos el bloqueo
        return response()->json(['mensaje' => 'Bloqueo de horario eliminado correctamente.']);
    }
}

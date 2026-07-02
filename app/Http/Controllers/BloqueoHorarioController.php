<?php

namespace App\Http\Controllers;

use App\Models\BloqueoHorario;
use App\Models\Veterinario;
use Illuminate\Http\Request;
use App\Http\Requests\GuardarBloqueoHorarioRequest;

class BloqueoHorarioController extends Controller
{
    public function crear(GuardarBloqueoHorarioRequest  $request, Veterinario $veterinario)
    {
        # Validar que el usuario sea Admin
        if (auth()->user()->rol->nombre_interno !== 'admin') {
            abort(403, 'No autorizado');
        }

        # Validamos los datos de entrada
        $request->validate();

        # Validamos que la hora de inicio y fin sean correctas
        if (($request->hora_inicio && !$request->hora_fin) || (!$request->hora_inicio && $request->hora_fin)) {
            return response()->json([
                'message' => 'Debe ingresar tanto la hora de inicio como la de fin, o dejar ambas vacías para bloquear el día completo.'
            ], 422);
        }

        # Validamos que la hora de inicio sea menor a la hora de fin
        if ($request->hora_inicio && $request->hora_fin && $request->hora_inicio >= $request->hora_fin) {
            return response()->json([
                'message' => 'La hora de fin debe ser posterior a la hora de inicio.'
            ], 422);
        }

        # Validamos que no exista un bloqueo en el mismo horario
        $bloqueoExistente = BloqueoHorario::where('veterinario_id', $veterinario->id)
            ->where('fecha_inicio', $request->fecha_inicio)
            ->where('hora_inicio', $request->hora_inicio)
            ->first();

        if ($bloqueoExistente) {
            return response()->json([
                'message' => 'Ya existe un bloqueo en el mismo horario.'
            ], 422);
        }

        # Creamos el bloqueo
        $bloqueo = BloqueoHorario::create([
            'veterinario_id' => $veterinario->id,
            'fecha_inicio' => $request->fecha_inicio,
            'fecha_fin' => $request->fecha_fin,
            'hora_inicio' => $request->hora_inicio,
            'hora_fin' => $request->hora_fin,
            'motivo' => $request->motivo,
        ]);

        # Retornamos el bloqueo
        return response()->json([
            'mensaje' => 'Bloqueo registrado correctamente.',
            'bloqueo' => $bloqueo
        ], 201);
    }

    public function eliminar(BloqueoHorario $bloqueo)
    {
        # Validar que el usuario sea Admin
        if (auth()->user()->rol->nombre_interno !== 'admin') {
            abort(403, 'No autorizado');
        }

        # Eliminamos el bloqueo
        $bloqueo->delete();

        # Retornamos el bloqueo
        return response()->json(['mensaje' => 'Bloqueo de horario eliminado correctamente.']);
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Cita;
use App\Models\EquipoMedico;
use Carbon\Carbon;
use App\Models\Rol;
use App\Models\User;
use Illuminate\Http\Request;

class EquipoMedicoController extends Controller
{
    /**
     * Devuelve los miembros del equipo médico de una cita.
     */
    public function porCita(Cita $cita)
    {
        $equipo = EquipoMedico::where('cita_id', $cita->id)
            ->with(['usuario', 'rol'])
            ->get();

        return response()->json($equipo);
    }

    /**
     * Agrega un miembro al equipo médico de una cita.
     * Solo el veterinario asignado o el admin pueden hacerlo.
     * La cita debe ser de categoría Cirugía.
     */
    public function agregar(Request $request, Cita $cita)
    {
        $this->autorizarGestion($cita);

        $this->validarCategoriaCirugia($cita);

        $validated = $request->validate([
            'usuario_id' => 'required|exists:users,id',
            'rol_id'     => 'required|exists:roles,id',
        ]);

        // Evitar duplicados: mismo usuario en la misma cita
        $existe = EquipoMedico::where('cita_id', $cita->id)
            ->where('usuario_id', $validated['usuario_id'])
            ->exists();


        if ($existe) {
            return response()->json([
                'error' => 'Este miembro ya está asignado a la cita.'
            ], 422);
        }

        $horaInicio = $cita->fecha_hora;
        $horaTermino = $cita->hora_termino ?? Carbon::parse($horaInicio)->addMinutes(60);

        // 1. Verificar si el usuario ya está asignado a otra cita/cirugía en el mismo horario
        $solapamientoEquipo = EquipoMedico::where('usuario_id', $validated['usuario_id'])
            ->where('cita_id', '!=', $cita->id)
            ->whereHas('cita', function ($q) use ($horaInicio, $horaTermino) {
                $q->where('estado', '!=', 'cancelada')
                    ->where('fecha_hora', '<', $horaTermino)
                    ->where('hora_termino', '>', $horaInicio);
            })
            ->exists();


        if ($solapamientoEquipo) {
            $inicioFormatted = Carbon::parse($horaInicio)->format('d-m-Y H:i');
            $finFormatted = Carbon::parse($horaTermino)->format('H:i');
            return response()->json([
                'error' => "El usuario ya tiene otra cita asignada en este horario ({$inicioFormatted} a {$finFormatted})."
            ], 422);
        }


        $miembro = EquipoMedico::create([
            'cita_id'    => $cita->id,
            'usuario_id' => $validated['usuario_id'],
            'rol_id'     => $validated['rol_id'],
        ]);

        return response()->json($miembro->load(['usuario', 'rol']), 201);
    }

    /**
     * Elimina un miembro del equipo médico.
     */
    public function eliminar(Cita $cita, EquipoMedico $miembro)
    {
        $this->autorizarGestion($cita);

        if ($miembro->cita_id !== $cita->id) {
            return response()->json(['error' => 'El miembro no pertenece a esta cita.'], 403);
        }

        $miembro->delete();

        return response()->json(['mensaje' => 'Miembro eliminado del equipo.']);
    }


    private function autorizarGestion(Cita $cita): void
    {
        $user = auth()->user();

        // Admin puede siempre
        if ($user->rol?->nombre_interno === 'admin') return;

        // El veterinario asignado puede gestionar
        if ($user->rol?->nombre_interno === 'veterinario') {
            $veterinario = $user->veterinario;
            if ($veterinario && $cita->veterinario_id === $veterinario->id) return;
        }

        abort(403, 'No tienes permiso para gestionar el equipo de esta cita.');
    }

    private function validarCategoriaCirugia(Cita $cita): void
    {
        $cita->load('prestacion.categoriaPrestacion');
        $nombreCategoria = $cita->prestacion?->categoriaPrestacion?->nombre ?? null;

        if ($nombreCategoria !== 'Cirugia') {
            abort(422, 'Solo se puede asignar equipo médico a citas de tipo Cirugía.');
        }
    }
}

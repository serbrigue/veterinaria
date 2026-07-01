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

    public function porCita(Cita $cita)
    {
        # Obtenemos los miembros del equipo médico
        $equipo = EquipoMedico::where('cita_id', $cita->id)
            ->with(['usuario', 'rol'])
            ->get();


        return response()->json($equipo);
    }

    public function agregar(Request $request, Cita $cita)
    {
        # Autorizamos la gestión
        $this->autorizarGestion($cita);

        # Validamos que sea una cita de cirugía
        $this->validarCategoriaCirugia($cita);

        # Validamos los datos
        $validated = $request->validate([
            'usuario_id' => 'required|exists:users,id',
            'rol_id'     => 'required|exists:roles,id',
        ]);

        # Verificamos que el usuario no esté duplicado
        $existe = EquipoMedico::where('cita_id', $cita->id)
            ->where('usuario_id', $validated['usuario_id'])
            ->exists();

        # Si el usuario está duplicado
        if ($existe) {
            return response()->json([
                'error' => 'Este miembro ya está asignado a la cita.'
            ], 422);
        }

        # Obtenemos las horas de la cita
        $horaInicio = $cita->fecha_hora;
        $horaTermino = $cita->hora_termino ?? Carbon::parse($horaInicio)->addMinutes(60);

        # Verificamos si el usuario está asignado a otra cita en el mismo horario
        $solapamientoEquipo = EquipoMedico::where('usuario_id', $validated['usuario_id'])
            ->where('cita_id', '!=', $cita->id)
            ->whereHas('cita', function ($q) use ($horaInicio, $horaTermino) {
                $q->where('estado', '!=', 'cancelada')
                    ->where('fecha_hora', '<', $horaTermino)
                    ->where('hora_termino', '>', $horaInicio);
            })
            ->exists();


        # Si el usuario está solapado, devolvemos error
        if ($solapamientoEquipo) {

            # Obtenemos las horas de la cita en formato legible
            $inicioFormatted = Carbon::parse($horaInicio)->format('d-m-Y H:i');
            $finFormatted = Carbon::parse($horaTermino)->format('H:i');

            # Devolvemos error
            return response()->json([
                'error' => "El usuario ya tiene otra cita asignada en este horario ({$inicioFormatted} a {$finFormatted})."
            ], 422);
        }


        # Creamos el miembro del equipo
        $miembro = EquipoMedico::create([
            'cita_id'    => $cita->id,
            'usuario_id' => $validated['usuario_id'],
            'rol_id'     => $validated['rol_id'],
        ]);

        return response()->json($miembro->load(['usuario', 'rol']), 201);
    }

    public function eliminar(Cita $cita, EquipoMedico $miembro)
    {
        # Autorizamos la gestión
        $this->autorizarGestion($cita);

        # Verificamos que el miembro pertenezca a la cita
        if ($miembro->cita_id !== $cita->id) {
            return response()->json(['error' => 'El miembro no pertenece a esta cita.'], 403);
        }

        # Eliminamos el miembro

        $miembro->delete();

        return response()->json(['mensaje' => 'Miembro eliminado del equipo.']);
    }


    private function autorizarGestion(Cita $cita): void
    {
        # Obtenemos el usuario actual
        $user = auth()->user();

        # Admin puede siempre
        if ($user->rol?->nombre_interno === 'admin') return;

        # El veterinario asignado puede gestionar
        if ($user->rol?->nombre_interno === 'veterinario') {
            $veterinario = $user->veterinario;
            if ($veterinario && $cita->veterinario_id === $veterinario->id) return;
        }

        abort(403, 'No tienes permiso para gestionar el equipo de esta cita.');
    }

    private function validarCategoriaCirugia(Cita $cita): void
    {
        # Cargamos la prestación y la categoría
        $cita->load('prestacion.categoriaPrestacion');

        # Obtenemos el nombre de la categoría
        $nombreCategoria = $cita->prestacion?->categoriaPrestacion?->nombre ?? null;

        # Si la categoría no es cirugía, devolvemos error
        if ($nombreCategoria !== 'Cirugia') {
            abort(422, 'Solo se puede asignar equipo médico a citas de tipo Cirugía.');
        }
    }
}

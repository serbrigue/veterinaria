<?php

namespace App\Http\Controllers;

use App\Models\Cita;
use App\Models\Cliente;
use App\Models\Mascota;
use App\Models\Sucursal;
use App\Models\Box;
use App\Models\Veterinario;
use App\Http\Requests\GuardarCitaRequest;
use App\Http\Requests\ActualizarCitaRequest;
use Inertia\Inertia;

class CitaController extends Controller
{

    public function listado()
    {
        $sucursales = Sucursal::with('boxes', 'veterinarios')->get();
        $mascotas = Mascota::with('cliente')->get();
        $citas = Cita::with('mascota.cliente.usuario', 'veterinario.usuario')->get();




        return Inertia::render('Cita/Listado', [
            'citas' => $citas,
            'mascotas' => $mascotas,
            'sucursales' => $sucursales,
        ]);
    }

    public function obtenerTodas()
    {
        /**
         * Intención de negocio:
         * Proveer el listado filtrado de citas para la API interna.
         * Sigue las mismas reglas de visibilidad y restricciones de rol que el listado web.
         */
        if (auth()->user()->isAdmin() || auth()->user()->isVeterinario()) {
            return Cita::with(['mascota.cliente.usuario', 'veterinario.usuario'])->get();
        }

        $clienteId = auth()->user()->cliente?->id;
        return Cita::whereHas('mascota', function ($query) use ($clienteId) {
            $query->where('cliente_id', $clienteId);
        })->with(['mascota.cliente.usuario', 'veterinario.usuario'])->get();
    }

    public function crear(GuardarCitaRequest $solicitud)
    {
        /**
         * Intención de negocio:
         * Registrar una nueva cita médica de forma consistente.
         * Dado que veterinario_id, box_id y hora_termino son campos obligatorios no-nulos en base de datos,
         * les asignamos un valor por defecto o calculado en caso de no especificarse por el cliente.
         */
        $data = $solicitud->validated();

        $defaultVet = \App\Models\Veterinario::first();
        $defaultBox = \App\Models\Box::first();
        $horaTermino = \Illuminate\Support\Carbon::parse($data['fecha_hora'])->addHour();

        $cita = Cita::create([
            'titulo' => $data['titulo'],
            'descripcion' => $data['descripcion'],
            'fecha_hora' => $data['fecha_hora'],
            'hora_termino' => $horaTermino,
            'estado' => 'pendiente',
            'mascota_id' => $data['mascota_id'],
            'veterinario_id' => $defaultVet?->id,
            'box_id' => $defaultBox?->id,
        ]);

        return response()->json($cita, 201);
    }

    public function actualizar(ActualizarCitaRequest $solicitud, Cita $cita)
    {
        /**
         * Intención de negocio:
         * Modificar los datos de la cita médica agendada.
         * La verificación de propiedad está delegada al middleware 'can' de la policy.
         */
        $cita->update($solicitud->validated());
        return response()->json($cita);
    }

    public function eliminar(Cita $cita)
    {
        /**
         * Intención de negocio:
         * Cancelar y remover la cita del sistema.
         * La validación de seguridad está delegada al middleware de la policy.
         */
        $cita->delete();
        return response()->json(['mensaje' => 'Cita eliminada correctamente']);
    }
}

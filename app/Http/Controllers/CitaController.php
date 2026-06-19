<?php

namespace App\Http\Controllers;

use App\Models\Cita;
use App\Models\Cliente;
use App\Models\Mascota;
use App\Http\Requests\GuardarCitaRequest;
use App\Http\Requests\ActualizarCitaRequest;
use Inertia\Inertia;

class CitaController extends Controller
{
    /**
     * MÓDULO 5 — Backend de referencia (no modificar).
     * Tu trabajo: migración, rutas web/api y Vue (axios + selects).
     * listado() envía mascotas y clientes para los select del formulario.
     */

    public function listado()
    {
        $userId = auth()->id();
        $citas = Cita::where('user_id', $userId)->get();

        return Inertia::render('Cita/Listado', [
            'citas' => $citas,
            'mascotas' => Mascota::where('user_id', $userId)->get(['id', 'nombre', 'sexo']),
            'clientes' => Cliente::where('user_id', $userId)->get(['id', 'nombre', 'email']),
        ]);
    }

    public function obtenerTodas()
    {
        return Cita::where('user_id', auth()->id())->get();
    }

    public function crear(GuardarCitaRequest $solicitud)
    {
        $cita = Cita::create([
            'titulo' => $solicitud->titulo,
            'descripcion' => $solicitud->descripcion,
            'fecha_hora' => $solicitud->fecha_hora,
            'mascota_id' => $solicitud->mascota_id,
            'cliente_id' => $solicitud->cliente_id,
            'user_id' => auth()->id(),
        ]);

        return response()->json($cita, 201);
    }

    public function actualizar(ActualizarCitaRequest $solicitud, Cita $cita)
    {
        if ($cita->user_id !== auth()->id()) {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        $cita->update($solicitud->validated());

        return response()->json($cita);
    }

    public function eliminar(Cita $cita)
    {
        if ($cita->user_id !== auth()->id()) {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        $cita->delete();

        return response()->json(['mensaje' => 'Cita eliminada correctamente']);
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Mascota;
use App\Http\Requests\GuardarMascotaRequest;
use App\Http\Requests\ActualizarMascotaRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Inertia\Inertia;

class MascotaController extends Controller
{
    /**
     * EJEMPLO DE REFERENCIA:
     * Este controlador tiene el CRUD completo en español.
     * Úsalo como guía para crear los demás controladores.
     */

    public function listado()
    {
        $mascotas = Mascota::where('user_id', auth()->id())->get();

        return Inertia::render('Mascota/Listado', [
            'mascotas' => $mascotas,
            'consultadoEn' => Carbon::now()->format('d/m/Y H:i'),
        ]);
    }

    public function obtenerTodas()
    {
        return Mascota::where('user_id', auth()->id())->get();
    }

    public function crear(GuardarMascotaRequest $solicitud)
    {
        $mascota = Mascota::create([
            ...$solicitud->validated(),
            'user_id' => auth()->id(),
        ]);

        return response()->json($mascota, 201);
    }

    public function actualizar(ActualizarMascotaRequest $solicitud, Mascota $mascota)
    {
        if ($mascota->user_id !== auth()->id()) {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        $mascota->update($solicitud->validated());

        return response()->json($mascota);
    }

    public function eliminar(Mascota $mascota)
    {
        if ($mascota->user_id !== auth()->id()) {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        $mascota->delete();

        return response()->json(['mensaje' => 'Mascota eliminada correctamente']);
    }
}

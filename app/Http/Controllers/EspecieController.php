<?php

namespace App\Http\Controllers;

use App\Models\Especie;
use App\Models\Raza;
use App\Http\Requests\GuardarEspecieRequest;
use App\Http\Requests\ActualizarEspecieRequest;
use Illuminate\Http\Request;
use Inertia\Inertia;

class EspecieController extends Controller
{
    /**
     * MÓDULO 2: Completa este controlador usando MascotaController como referencia.
     */

    public function listado(Request $request)
    {
        /**
         * Intención de negocio:
         * Proveer una respuesta dinámica de especies que permita a los usuarios
         * buscar especies específicas de forma rápida y optimizada,
         * soportando tanto peticiones HTTP tradicionales (Inertia) como llamadas Axios asíncronas (JSON).
         */
        $query = Especie::query();

        if ($request->filled('texto')) {
            $query->where('nombre', 'like', '%' . $request->texto . '%');
        }

        $especies = $query->orderBy('nombre')->get();

        if ($request->wantsJson()) {
            return response()->json([
                'especies' => $especies,
            ]);
        }

        return Inertia::render('Especie/Listado', [
            'especies' => $especies,
        ]);
    }

    public function obtenerTodas()
    {
        $especies = Especie::orderBy('nombre')->get();

        if (request()->wantsJson()) {
            return response()->json([
                'especies' => $especies,
            ]);
        }

        return $especies;
    }

    public function crear(GuardarEspecieRequest $solicitud)
    {
        /**
         * Intención de negocio:
         * Guardar el ID del usuario logueado en 'creado_por' para mantener la auditoría
         * de quién registró la especie en el sistema.
         */
        $data = $solicitud->validated();
        $data['creado_por'] = auth()->id();

        $especie = Especie::create($data);

        return response()->json($especie, 201);
    }

    public function actualizar(ActualizarEspecieRequest $solicitud, Especie $especie)
    {
        $especie->update($solicitud->validated());
        return response()->json($especie);
    }

    public function eliminar(Especie $especie)
    {
        $especie->delete();
        return response()->json(['mensaje' => 'Especie eliminada correctamente']);
    }

    public function detalle(Especie $especie)
    {
        return Inertia::render('Especie/Detalle', [
            'especie' => $especie,
            'razas' => $especie->razas,
        ]);
    }
}

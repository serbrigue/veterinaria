<?php

namespace App\Http\Controllers;

use App\Models\Raza;
use App\Models\Especie;
use App\Http\Requests\GuardarRazaRequest;
use App\Http\Requests\ActualizarRazaRequest;
use Illuminate\Http\Request;

use Inertia\Inertia;
use Illuminate\Support\Facades\Cache;

class RazaController extends Controller
{
    /**
     * MÓDULO 3: Completa este controlador usando MascotaController como referencia.
     */

    public function listado(Request $request)
    {

        $filtroEspecie = $request->input('especie_id');

        $razasCached = Cache::remember('razas_full', now()->addMinutes(30), function() {
            return Raza::with('especie')->get();
        });
        
        $especiesCached = Cache::remember('especies_simple', now()->addMinutes(30), function() {
            return Especie::all();
        });

        if (request()->wantsJson()) {
            $razas = $razasCached;
            if ($filtroEspecie) {
                $razas = $razas->where('especie_id', $filtroEspecie)->values();
            }

            return response()->json([
                'razas' => $razas,
                'especies' => $especiesCached,
            ]);
        }

        return Inertia::render('Raza/Listado', [
            'razas' => $razasCached,
            'especies' => $especiesCached,
        ]);
    }

    public function obtenerTodas()
    {
        // TODO: Retornar todas las razas del usuario autenticado
        // return Raza::where('user_id', auth()->id())->get();
        return Raza::orderBy('nombre')->get();
    }

    public function crear(GuardarRazaRequest $solicitud)
    {

        $data = $solicitud->validated();
        $data['creado_por'] = auth()->id();

        $raza = Raza::create($data);
        return response()->json($raza, 201);
    }

    public function actualizar(ActualizarRazaRequest $solicitud, Raza $raza)
    {
        $raza->update($solicitud->validated());
        return response()->json($raza);
    }

    public function eliminar(Raza $raza)
    {
        $raza->delete();
        return response()->json(['mensaje' => 'Raza eliminada correctamente']);
    }

    public function detalle(Raza $raza)
    {
        /**
         * Intención de negocio:
         * Cargar los detalles de una raza específica incluyendo la especie a la que pertenece
         * para renderizar la vista de detalle en Inertia.
         */;

        return Inertia::render('Raza/Detalle', [
            'raza' => $raza,
            'especie' => $raza->especie,
        ]);
    }
}

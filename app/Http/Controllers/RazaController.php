<?php

namespace App\Http\Controllers;

use App\Models\Raza;
use App\Models\Especie;
use App\Http\Requests\GuardarRazaRequest;
use App\Http\Requests\ActualizarRazaRequest;
use Illuminate\Http\Request;

use Inertia\Inertia;

class RazaController extends Controller
{
    /**
     * MÓDULO 3: Completa este controlador usando MascotaController como referencia.
     */

    public function listado(Request $request)
    {

        $filtroEspecie = $request->input('especie_id');


        if (request()->wantsJson()) {
            $razas = Raza::with('especie');
            if ($filtroEspecie) {
                $razas->where('especie_id', $filtroEspecie);
            }

            return response()->json([
                'razas' => $razas->get(),
                'especies' => Especie::all(),
            ]);
        }

        return Inertia::render('Raza/Listado', [
            'razas' => Raza::with('especie')->get(),
            'especies' => Especie::all(),
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
        // TODO: Verificar que $raza->user_id === auth()->id(), actualizar, retornar JSON
        if ($raza->user_id !== auth()->id()) {
            return response()->json(['error' => 'No autorizado'], 403);
        }
        $raza->update($solicitud->validated());
        return response()->json($raza);
    }

    public function eliminar(Raza $raza)
    {
        if ($raza->user_id !== auth()->id()) {
            return response()->json(['error' => 'No autorizado'], 403);
        }
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

<?php

namespace App\Http\Controllers;

use App\Models\Insumo;
use App\Models\Sucursal;
use App\Http\Requests\GuardarInsumoRequest;
use App\Http\Requests\ActualizarInsumoRequest;
use Illuminate\Http\Request;

use Inertia\Inertia;

class InsumoController extends Controller
{
    /**
     * MÓDULO 3: Completa este controlador usando MascotaController como referencia.
     */

    public function listado(Request $request)
    {
        if (request()->wantsJson()) {
            $insumos = Insumo::with('sucursal')->get();
            return response()->json([
                'insumos' => $insumos,
            ]);
        }

        return Inertia::render('Insumo/Listado', [
            'insumos' => Insumo::with('sucursal')->get(),
            'sucursales' => Sucursal::all(),
        ]);
    }

    public function obtenerTodas()
    {
        return Insumo::with('sucursal')->orderBy('nombre')->get();
    }

    public function crear(GuardarInsumoRequest $solicitud)
    {

        $data = $solicitud->validated();

        $insumo = Insumo::create($data);
        return response()->json($insumo, 201);
    }

    public function actualizar(ActualizarInsumoRequest $solicitud, Insumo $insumo)
    {

        $insumo->update($solicitud->validated());
        return response()->json($insumo);
    }

    public function eliminar(Insumo $insumo)
    {

        $insumo->delete();
        return response()->json(['mensaje' => 'Insumo eliminado correctamente']);
    }

    public function detalle(Insumo $insumo)
    {

        return Inertia::render('Insumo/Detalle', [
            'insumo' => $insumo,
        ]);
    }
}

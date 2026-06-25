<?php

namespace App\Http\Controllers;

use App\Models\Insumo;
use App\Models\Sucursal;
use App\Http\Requests\GuardarInsumoRequest;
use App\Http\Requests\ActualizarInsumoRequest;
use Illuminate\Http\Request;

use Inertia\Inertia;
use Illuminate\Support\Facades\Cache;

class InsumoController extends Controller
{
    /**
     * MÓDULO 3: Completa este controlador usando MascotaController como referencia.
     */

    public function listado(Request $request)
    {
        $insumosCached = Cache::remember('insumos_full', now()->addMinutes(30), function() {
            return Insumo::with('sucursal')->orderBy('nombre')->get();
        });

        if (request()->wantsJson()) {
            return response()->json([
                'insumos' => $insumosCached,
            ]);
        }

        return Inertia::render('Insumo/Listado', [
            'insumos' => $insumosCached,
            'sucursales' => Cache::remember('sucursales_simple', now()->addMinutes(30), fn() => Sucursal::all()),
        ]);
    }

    public function obtenerTodas()
    {
        return Cache::remember('insumos_full', now()->addMinutes(30), function() {
            return Insumo::with('sucursal')->orderBy('nombre')->get();
        });
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

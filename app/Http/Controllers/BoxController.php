<?php

namespace App\Http\Controllers;

use App\Models\Box;
use App\Models\Sucursal;
use App\Http\Requests\GuardarBoxRequest;
use App\Http\Requests\ActualizarBoxRequest;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Cache;

class BoxController extends Controller
{

    public function listado(Request $request)
    {
        $boxes = Cache::remember('boxes_full', now()->addMinutes(30), function() {
            return Box::with('sucursal')->orderBy('nombre')->get();
        });
        $sucursales = Cache::remember('sucursales_simple', now()->addMinutes(30), function() {
            return Sucursal::orderBy('nombre')->get();
        });


        if ($request->wantsJson()) {
            return response()->json([
                'boxes' => $boxes,
                'sucursales' => $sucursales,
            ]);
        }

        return Inertia::render('Box/Listado', [
            'boxes' => $boxes,
            'sucursales' => $sucursales,
        ]);
    }

    public function obtenerTodas()
    {
        $boxes = Cache::remember('boxes_simple', now()->addMinutes(30), function() {
            return Box::orderBy('nombre')->get();
        });

        if (request()->wantsJson()) {
            return response()->json([
                'boxes' => $boxes,
            ]);
        }

        return $boxes;
    }

    public function crear(GuardarBoxRequest $solicitud)
    {

        $data = $solicitud->validated();
        $data['creado_por'] = auth()->id();

        $box = Box::create($data);

        return response()->json($box, 201);
    }

    public function actualizar(ActualizarBoxRequest $solicitud, Box $box)
    {
        $box->update($solicitud->validated());
        return response()->json($box);
    }

    public function eliminar(Box $box)
    {
        $box->delete();
        return response()->json(['mensaje' => 'Box eliminado correctamente']);
    }

    public function detalle(Box $box)
    {
        return Inertia::render('Box/Detalle', [
            'box' => $box,

        ]);
    }
}

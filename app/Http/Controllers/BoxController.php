<?php

namespace App\Http\Controllers;

use App\Models\Box;
use App\Models\Sucursal;
use App\Http\Requests\GuardarBoxRequest;
use App\Http\Requests\ActualizarBoxRequest;
use Illuminate\Http\Request;
use Inertia\Inertia;

class BoxController extends Controller
{

    public function listado(Request $request)
    {
        $boxes = Box::with('sucursal')->orderBy('nombre')->get();
        $sucursales = Sucursal::orderBy('nombre')->get();


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
        $boxes = Box::orderBy('nombre')->get();

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

<?php

namespace App\Http\Controllers;

use App\Models\Sucursal;
use App\Http\Requests\GuardarSucursalRequest;
use App\Http\Requests\ActualizarSucursalRequest;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SucursalController extends Controller
{

    public function listado(Request $request)
    {
        $sucursales = Sucursal::orderBy('nombre')->get();

        if ($request->wantsJson()) {
            return response()->json([
                'sucursales' => $sucursales,
            ]);
        }

        return Inertia::render('Sucursal/Listado', [
            'sucursales' => $sucursales,
        ]);
    }

    public function obtenerTodas()
    {
        $sucursales = Sucursal::orderBy('nombre')->get();

        if (request()->wantsJson()) {
            return response()->json([
                'sucursales' => $sucursales,
            ]);
        }

        return $sucursales;
    }

    public function crear(GuardarSucursalRequest $solicitud)
    {

        $data = $solicitud->validated();
        $data['creado_por'] = auth()->id();

        $sucursal = Sucursal::create($data);

        return response()->json($sucursal, 201);
    }

    public function actualizar(ActualizarSucursalRequest $solicitud, Sucursal $sucursal)
    {
        $sucursal->update($solicitud->validated());
        return response()->json($sucursal);
    }

    public function eliminar(Sucursal $sucursal)
    {
        $sucursal->delete();
        return response()->json(['mensaje' => 'Sucursal eliminada correctamente']);
    }

    public function detalle(Sucursal $sucursal)
    {
        $veterinarios = $sucursal->veterinarios;
        $boxes = $sucursal->boxes;


        return Inertia::render('Sucursal/Detalle', [
            'sucursal' => $sucursal,
            'veterinarios' => $veterinarios,
            'boxes' => $boxes,
        ]);
    }
}

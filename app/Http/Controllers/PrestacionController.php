<?php

namespace App\Http\Controllers;

use App\Models\Prestacion;
use App\Models\Sucursal;
use App\Http\Requests\GuardarPrestacionRequest;
use App\Http\Requests\ActualizarPrestacionRequest;
use Illuminate\Http\Request;

use Inertia\Inertia;

class PrestacionController extends Controller
{

    public function listado(Request $request)
    {
        if (request()->wantsJson()) {
            $prestaciones = Prestacion::with(['sucursal', 'especialidad'])->get();

            return response()->json([
                'prestaciones' => $prestaciones,

            ]);
        }

        return Inertia::render('Prestacion/Listado', [
            'prestaciones' => Prestacion::with(['sucursal', 'especialidad'])->get(),
            'sucursales' => Sucursal::all(),
        ]);
    }

    public function obtenerTodas()
    {
        return Prestacion::with(['sucursal', 'especialidad'])->orderBy('nombre')->get();
    }

    public function crear(GuardarPrestacionRequest $solicitud)
    {

        $data = $solicitud->validated();
        $prestacion = Prestacion::create($data);
        return response()->json($prestacion, 201);
    }

    public function actualizar(ActualizarPrestacionRequest $solicitud, Prestacion $prestacion)
    {
        $prestacion->update($solicitud->validated());
        return response()->json($prestacion);
    }

    public function eliminar(Prestacion $prestacion)
    {
        $prestacion->delete();
        return response()->json(['mensaje' => 'Prestacion eliminada correctamente']);
    }

    public function detalle(Prestacion $prestacion)
    {
        $especialidad = $prestacion->especialidad;
        return Inertia::render('Prestacion/Detalle', [
            'prestacion' => $prestacion,
            'especialidad' => $especialidad,
            'sucursal' => $prestacion->sucursal,
        ]);
    }
}

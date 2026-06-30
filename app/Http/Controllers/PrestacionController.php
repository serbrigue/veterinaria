<?php

namespace App\Http\Controllers;

use App\Models\Prestacion;
use App\Models\Sucursal;
use App\Http\Requests\GuardarPrestacionRequest;
use App\Http\Requests\ActualizarPrestacionRequest;
use Illuminate\Http\Request;

use Inertia\Inertia;
use Illuminate\Support\Facades\Cache;

class PrestacionController extends Controller
{

    public function listado(Request $request)
    {
        $query = Prestacion::with(['sucursal', 'especialidad', 'categoriaPrestacion'])
            ->when($request->filled('especialidad_id'), function ($q) use ($request) {
                if ($request->especialidad_id === 'general') {
                    $q->whereNull('especialidad_id');
                } else {
                    $q->where('especialidad_id', $request->especialidad_id);
                }
            })
            ->when($request->filled('sucursal_id'), fn($q) => $q->where('sucursal_id', $request->sucursal_id))
            ->when($request->filled('orden_precio'), function ($q) use ($request) {
                if ($request->orden_precio === 'asc') {
                    $q->orderBy('precio_base', 'asc');
                } elseif ($request->orden_precio === 'desc') {
                    $q->orderBy('precio_base', 'desc');
                }
            });

        $prestaciones = $query->get();

        if ($request->wantsJson()) {
            return response()->json([
                'prestaciones' => $prestaciones,
            ]);
        }

        return Inertia::render('Prestacion/Listado', [
            'prestaciones' => $prestaciones,
            'sucursales' => Cache::remember('sucursales_simple', now()->addMinutes(30), fn() => Sucursal::all()),
            'especialidades' => Cache::remember('especialidades_simple', now()->addMinutes(30), fn() => \App\Models\Especialidad::all()),
            'categoriasPrestaciones' => Cache::remember('categorias_prestaciones_simple', now()->addMinutes(30), fn() => \App\Models\CategoriaPrestacion::all()),
        ]);
    }

    public function obtenerTodas()
    {
        return Cache::remember('prestaciones_full', now()->addMinutes(30), function() {
            return Prestacion::with(['sucursal', 'especialidad', 'categoriaPrestacion'])->orderBy('nombre')->get();
        });
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
        $prestacion->load(['sucursal', 'especialidad', 'categoriaPrestacion']);
        $especialidad = $prestacion->especialidad;
        return Inertia::render('Prestacion/Detalle', [
            'prestacion' => $prestacion,
            'especialidad' => $especialidad,
            'sucursal' => $prestacion->sucursal,
        ]);
    }
}

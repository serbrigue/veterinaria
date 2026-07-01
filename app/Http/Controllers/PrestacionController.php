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
        # Iniciamos la consulta con las relaciones y filtros
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

        #Obtenemos las prestaciones
        $prestaciones = $query->get();

        #Si la petición es JSON
        if ($request->wantsJson()) {
            return response()->json([
                'prestaciones' => $prestaciones,
            ]);
        }

        #Devolvemos la vista con las prestaciones
        return Inertia::render('Prestacion/Listado', [
            'prestaciones' => $prestaciones,
            'sucursales' => Cache::remember('sucursales_simple', now()->addMinutes(30), fn() => Sucursal::all()),
            'especialidades' => Cache::remember('especialidades_simple', now()->addMinutes(30), fn() => \App\Models\Especialidad::all()),
            'categoriasPrestaciones' => Cache::remember('categorias_prestaciones_simple', now()->addMinutes(30), fn() => \App\Models\CategoriaPrestacion::all()),
        ]);
    }

    public function obtenerTodas()
    {
        #Obtenemos todas las prestaciones con sus relaciones y las cacheamos
        return Cache::remember('prestaciones_full', now()->addMinutes(30), function () {
            return Prestacion::with(['sucursal', 'especialidad', 'categoriaPrestacion'])->orderBy('nombre')->get();
        });
    }

    public function crear(GuardarPrestacionRequest $solicitud)
    {
        #Validamos la solicitud
        $data = $solicitud->validated();
        #Creamos la prestacion
        $prestacion = Prestacion::create($data);
        #Devolvemos la prestacion
        return response()->json($prestacion, 201);
    }

    public function actualizar(ActualizarPrestacionRequest $solicitud, Prestacion $prestacion)
    {
        #Actualizamos la prestacion
        $prestacion->update($solicitud->validated());
        #Devolvemos la prestacion
        return response()->json($prestacion);
    }

    public function eliminar(Prestacion $prestacion)
    {
        #Eliminamos la prestacion
        $prestacion->delete();
        #Devolvemos un mensaje de exito
        return response()->json(['mensaje' => 'Prestacion eliminada correctamente']);
    }

    public function detalle(Prestacion $prestacion)
    {
        #Cargamos las relaciones de la prestacion
        $prestacion->load(['sucursal', 'especialidad', 'categoriaPrestacion']);
        #Obtenemos la especialidad
        $especialidad = $prestacion->especialidad;
        #Devolvemos la vista con los datos
        return Inertia::render('Prestacion/Detalle', [
            'prestacion' => $prestacion,
            'especialidad' => $especialidad,
            'sucursal' => $prestacion->sucursal,
        ]);
    }
}

<?php

namespace App\Http\Controllers;

use App\Http\Requests\ActualizarBoxRequest;
use App\Http\Requests\GuardarBoxRequest;
use App\Models\Box;
use App\Models\CategoriaPrestacion;
use App\Models\Sucursal;
use App\Traits\HandlesPhotoUploads;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;

class BoxController extends Controller
{
    use HandlesPhotoUploads;
    public function listado(Request $request)
    {
        // Obtenemos todos los boxes con eager loading y lo cargamos en caché por 30 minutos
        $boxes = Cache::remember('boxes_full', now()->addMinutes(30), function () {
            // Si no existe el box en la caché, lo obtenemos de la base de datos
            return Box::with('sucursal', 'categoriaPrestacion')->orderBy('nombre')->get();
        });

        // Obtenemos todas las sucursales con eager loading y lo cargamos en caché por 30 minutos
        $sucursales = Cache::remember('sucursales_simple', now()->addMinutes(30), function () {
            // Si no existe la sucursal en la caché, la obtenemos de la base de datos
            return Sucursal::orderBy('nombre')->get();
        });

        // Obtenemos todas las categorias de prestacion con eager loading y lo cargamos en caché por 60 minutos
        $categoriasPrestacion = Cache::remember('categorias_prestaciones_full', now()->addMinutes(60), function () {
            // Si no existe la categoria de prestacion en la caché, la obtenemos de la base de datos
            return CategoriaPrestacion::orderBy('nombre')->get();
        });

        // Verificamos si la solicitud es en formato JSON
        if ($request->wantsJson()) {
            $boxesFiltrados = $boxes;
            
            if ($request->input('texto')) {
                $texto = strtolower($request->input('texto'));
                $boxesFiltrados = $boxesFiltrados->filter(function($box) use ($texto) {
                    return str_contains(strtolower($box->nombre), $texto);
                })->values();
            }
            
            if ($request->input('categoria_prestacion_id')) {
                $boxesFiltrados = $boxesFiltrados->where('categoria_prestacion_id', $request->input('categoria_prestacion_id'))->values();
            }

            // Si es JSON, devolvemos los datos en formato JSON
            return response()->json([
                'boxes' => $boxesFiltrados,
                'sucursales' => $sucursales,
                'categoriasPrestacion' => $categoriasPrestacion,
            ]);
        }

        // Devolvemos la vista con los datos
        return Inertia::render('Box/Listado', [
            'boxes' => $boxes,
            'sucursales' => $sucursales,
            'categoriasPrestacion' => $categoriasPrestacion,
        ]);
    }

    public function obtenerTodas()
    {
        // Obtenemos todos los boxes sin eager loading y lo cargamos en caché por 30 minutos
        $boxes = Cache::remember('boxes_simple', now()->addMinutes(30), function () {
            // Si no existe el box en la caché, lo obtenemos de la base de datos
            return Box::orderBy('nombre')->get();
        });

        // Verificamos si la solicitud es en formato JSON
        if (request()->wantsJson()) {
            return response()->json([
                'boxes' => $boxes,
            ]);
        }

        // Devolvemos los boxes
        return $boxes;
    }

    public function crear(GuardarBoxRequest $solicitud)
    {
        // Validamos la solicitud
        $data = $solicitud->validated();
        $data['creado_por'] = auth()->id();

        if ($solicitud->hasFile('imagen_url')) {
            $data['imagen_url'] = $this->procesarFoto($solicitud, 'imagen_url', 'boxes/fotos');
        }

        // Creamos el box
        $box = Box::create($data);

        return response()->json($box, 201);
    }

    public function actualizar(ActualizarBoxRequest $solicitud, Box $box)
    {
        // Validamos la solicitud
        $data = $solicitud->validated();

        if ($solicitud->hasFile('imagen_url')) {
            $data['imagen_url'] = $this->procesarFoto($solicitud, 'imagen_url', 'boxes/fotos', $box->imagen_url);
        }

        // Actualizamos el box
        $box->update($data);

        // Devolvemos el box
        return response()->json($box);
    }

    public function eliminar(Box $box)
    {
        $this->eliminarFotoFisica($box->imagen_url);

        // Eliminamos el box
        $box->delete();

        // Devolvemos un mensaje de éxito
        return response()->json(['mensaje' => 'Box eliminado correctamente']);
    }

    public function detalle(Box $box)
    {
        // Cargamos el box, sucursal y categoria
        $box->load('sucursal', 'categoriaPrestacion');

        // Devolvemos la vista con el box
        return Inertia::render('Box/Detalle', [
            'box' => $box,
        ]);
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Insumo;
use App\Models\Sucursal;
use App\Models\CategoriaInsumo;
use App\Http\Requests\GuardarInsumoRequest;
use App\Http\Requests\ActualizarInsumoRequest;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Cache;

class InsumoController extends Controller
{

    public function listado(Request $request)
    {

        # Obtenemos todos los insumos con eager loading y lo cargamos en caché por 30 minutos
        $insumosCached = Cache::remember('insumos_full', now()->addMinutes(30), function () {
            return Insumo::with('sucursal', 'categoriaInsumo')->orderBy('nombre')->get();
        });

        # Obtenemos todas las categorias de insumos con eager loading y lo cargamos en caché por 60 minutos
        $categoriasInsumo = Cache::remember('categorias_insumos_full', now()->addMinutes(60), function () {
            return CategoriaInsumo::orderBy('nombre')->get();
        });

        # Verificamos si la solicitud es JSON
        if (request()->wantsJson()) {
            return response()->json([
                'insumos'         => $insumosCached,
                'categoriasInsumo' => $categoriasInsumo,
            ]);
        }

        # Devolvemos la vista con los datos
        return Inertia::render('Insumo/Listado', [
            'insumos'         => $insumosCached,
            'sucursales'      => Cache::remember('sucursales_simple', now()->addMinutes(30), fn() => Sucursal::all()),
            'categoriasInsumo' => $categoriasInsumo,
        ]);
    }

    public function obtenerTodas()
    {
        # Obtenemos todos los insumos con eager loading y lo cargamos en caché por 30 minutos
        return Cache::remember('insumos_full', now()->addMinutes(30), function () {
            return Insumo::with('sucursal')->orderBy('nombre')->get();
        });
    }

    public function crear(GuardarInsumoRequest $solicitud)
    {
        # Validamos la solicitud
        $data = $solicitud->validated();

        # Creamos el insumo
        $insumo = Insumo::create($data);

        # Devolvemos el insumo
        return response()->json($insumo, 201);
    }

    public function actualizar(ActualizarInsumoRequest $solicitud, Insumo $insumo)
    {
        # Actualizamos el insumo
        $insumo->update($solicitud->validated());

        # Devolvemos el insumo
        return response()->json($insumo);
    }

    public function eliminar(Insumo $insumo)
    {
        # Eliminamos el insumo
        $insumo->delete();

        # Devolvemos un mensaje de éxito
        return response()->json(['mensaje' => 'Insumo eliminado correctamente']);
    }

    public function detalle(Insumo $insumo)
    {
        # Cargamos las relaciones del insumo
        $insumo->load('sucursal', 'categoriaInsumo');

        # Devolvemos la vista
        return Inertia::render('Insumo/Detalle', [
            'insumo' => $insumo,
        ]);
    }
}

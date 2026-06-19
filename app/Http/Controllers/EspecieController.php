<?php

namespace App\Http\Controllers;

use App\Models\Especie;
use App\Http\Requests\GuardarEspecieRequest;
use App\Http\Requests\ActualizarEspecieRequest;
use Inertia\Inertia;

class EspecieController extends Controller
{
    /**
     * MÓDULO 2: Completa este controlador usando MascotaController como referencia.
     */

    public function listado()
    {
        // TODO: Obtener especies del usuario autenticado y retornar la vista Inertia
        // return Inertia::render('Especie/Listado', [
        //     'especies' => Especie::where('user_id', auth()->id())->get(),
        // ]);
    }

    public function obtenerTodas()
    {
        // TODO: Retornar todas las especies del usuario autenticado
        // return Especie::where('user_id', auth()->id())->get();
    }

    public function crear(GuardarEspecieRequest $solicitud)
    {
        // TODO: Validar con $solicitud->validated(), crear la especie con auth()->id(), retornar JSON 201
        // $especie = Especie::create([...]);
        // return response()->json($especie, 201);
    }

    public function actualizar(ActualizarEspecieRequest $solicitud, Especie $especie)
    {
        // TODO: Verificar que $especie->user_id === auth()->id(), actualizar, retornar JSON
        // if ($especie->user_id !== auth()->id()) {
        //     return response()->json(['error' => 'No autorizado'], 403);
        // }
        // $especie->update($solicitud->validated());
        // return response()->json($especie);
    }

    public function eliminar(Especie $especie)
    {
        // TODO: Verificar ownership, eliminar, retornar JSON
        // if ($especie->user_id !== auth()->id()) {
        //     return response()->json(['error' => 'No autorizado'], 403);
        // }
        // $especie->delete();
        // return response()->json(['mensaje' => 'Especie eliminada correctamente']);
    }
}

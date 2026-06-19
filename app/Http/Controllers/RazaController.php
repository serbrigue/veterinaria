<?php

namespace App\Http\Controllers;

use App\Models\Raza;
use App\Http\Requests\GuardarRazaRequest;
use App\Http\Requests\ActualizarRazaRequest;
use Inertia\Inertia;

class RazaController extends Controller
{
    /**
     * MÓDULO 3: Completa este controlador usando MascotaController como referencia.
     */

    public function listado()
    {
        // TODO: Obtener razas del usuario autenticado y retornar la vista Inertia
        // return Inertia::render('Raza/Listado', [
        //     'razas' => Raza::where('user_id', auth()->id())->get(),
        // ]);
    }

    public function obtenerTodas()
    {
        // TODO: Retornar todas las razas del usuario autenticado
        // return Raza::where('user_id', auth()->id())->get();
    }

    public function crear(GuardarRazaRequest $solicitud)
    {
        // TODO: Validar, crear la raza con auth()->id(), retornar JSON 201
        // $raza = Raza::create([...]);
        // return response()->json($raza, 201);
    }

    public function actualizar(ActualizarRazaRequest $solicitud, Raza $raza)
    {
        // TODO: Verificar que $raza->user_id === auth()->id(), actualizar, retornar JSON
        // if ($raza->user_id !== auth()->id()) {
        //     return response()->json(['error' => 'No autorizado'], 403);
        // }
        // $raza->update($solicitud->validated());
        // return response()->json($raza);
    }

    public function eliminar(Raza $raza)
    {
        // TODO: Verificar ownership, eliminar, retornar JSON
        // if ($raza->user_id !== auth()->id()) {
        //     return response()->json(['error' => 'No autorizado'], 403);
        // }
        // $raza->delete();
        // return response()->json(['mensaje' => 'Raza eliminada correctamente']);
    }
}

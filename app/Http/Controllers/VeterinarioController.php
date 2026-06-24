<?php

namespace App\Http\Controllers;

use App\Models\Veterinario;
use App\Models\User;
use App\Models\Especialidad;
use App\Models\Sucursal;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\GuardarVeterinarioRequest;
use App\Http\Requests\ActualizarVeterinarioRequest;
use Illuminate\Http\Request;
use Inertia\Inertia;

class VeterinarioController extends Controller
{
    /**
     * MÓDULO 4 — Backend de referencia (no modificar).
     * Tu trabajo: migración, rutas web/api y Vue (axios).
     */
    public function listado(Request $request)
    {
        $query = Veterinario::with(['usuario', 'sucursal', 'especialidad'])
            ->when($request->filled('nombre'), fn($q) => $q->whereHas('usuario', fn($u) => $u->where('name', 'like', '%' . $request->nombre . '%')))
            ->when($request->filled('especialidad_id'), fn($q) => $q->where('especialidad_id', $request->especialidad_id))
            ->when($request->filled('sucursal_id'), fn($q) => $q->where('sucursal_id', $request->sucursal_id));

        $veterinarios = $query->get();
        $sucursales = Sucursal::all();
        $especialidades = Especialidad::all();

        if (request()->wantsJson()) {
            return response()->json([
                'veterinarios' => $veterinarios,
            ]);
        }

        return Inertia::render('Veterinario/Listado', [
            'veterinarios' => $veterinarios,
            'sucursales' => $sucursales,
            'especialidades' => $especialidades,
        ]);
    }

    public function detalle(Veterinario $veterinario)
    {
        $veterinario->load(['usuario', 'sucursal', 'especialidad']);

        return Inertia::render('Veterinario/Detalle', [
            'veterinario' => $veterinario,
        ]);
    }

    public function obtenerTodas()
    {
        return Veterinario::with(['usuario', 'sucursal', 'especialidad'])->get();
    }

    public function crear(GuardarVeterinarioRequest $solicitud)
    {
        $data = $solicitud->validated();

        $usuario = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'rol_id' => 2,
        ]);

        $veterinario = Veterinario::create([
            'user_id' => $usuario->id,
            'especialidad_id' => $data['especialidad_id'],
            'foto_perfil_url' => $data['foto_perfil_url'] ?? null,
            'sucursal_id' => $data['sucursal_id'],
            'telefono' => $data['telefono'] ?? null,
            'direccion' => $data['direccion'] ?? null,
        ]);

        return response()->json($veterinario, 201);
    }

    public function actualizar(ActualizarVeterinarioRequest $solicitud, Veterinario $veterinario)
    {
        $veterinario->update($solicitud->validated());
        return response()->json($veterinario);
    }

    public function eliminar(Veterinario $veterinario)
    {
        // Al eliminar al veterinario, también eliminamos su usuario asociado para no dejar registros huérfanos
        $usuario = $veterinario->usuario;
        $veterinario->delete();
        if ($usuario) {
            $usuario->delete();
        }

        return response()->json(['mensaje' => 'Veterinario eliminado correctamente']);
    }
}

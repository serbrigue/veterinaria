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
use Illuminate\Support\Facades\Cache;

class VeterinarioController extends Controller
{
    public function listado(Request $request)
    {
        #Obtenemos todos los veterinarios con sus relaciones y filtramos
        $query = Veterinario::with(['usuario', 'sucursal', 'especialidad'])
            ->when($request->filled('nombre'), fn($q) => $q->whereHas('usuario', fn($u) => $u->where('name', 'like', '%' . $request->nombre . '%')))
            ->when($request->filled('especialidad_id'), fn($q) => $q->where('especialidad_id', $request->especialidad_id))
            ->when($request->filled('sucursal_id'), fn($q) => $q->where('sucursal_id', $request->sucursal_id));

        #Obtenemos todos los veterinarios
        $veterinarios = $query->get();

        #Obtenemos todas las sucursales
        $sucursales = Cache::remember('sucursales_simple', now()->addMinutes(30), function () {
            return Sucursal::all();
        });

        #Obtenemos todas las especialidades
        $especialidades = Cache::remember('especialidades_simple', now()->addMinutes(30), function () {
            return Especialidad::all();
        });

        #Si la peticion es JSON
        if (request()->wantsJson()) {
            return response()->json([
                'veterinarios' => $veterinarios,
            ]);
        }

        #Devolvemos la vista con las sucursales, especialidades y veterinarios
        return Inertia::render('Veterinario/Listado', [
            'veterinarios' => $veterinarios,
            'sucursales' => $sucursales,
            'especialidades' => $especialidades,
        ]);
    }

    public function detalle(Veterinario $veterinario)
    {
        #Cargamos las relaciones necesarias para el detalle
        $veterinario->load(['usuario', 'sucursal', 'especialidad']);

        #Devolvemos la vista con los datos
        return Inertia::render('Veterinario/Detalle', [
            'veterinario' => $veterinario,
        ]);
    }

    public function obtenerTodas()
    {
        #Obtenemos todos los veterinarios
        return Cache::remember('veterinarios_full', now()->addMinutes(30), function () {
            return Veterinario::with(['usuario', 'sucursal', 'especialidad'])->get();
        });
    }

    public function crear(GuardarVeterinarioRequest $solicitud)
    {
        #Obtenemos los datos validados
        $data = $solicitud->validated();

        #Creamos el usuario
        $usuario = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'rol_id' => 2,
        ]);

        #Creamos el veterinario
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
        #Obtenemos los datos validados
        $veterinario->update($solicitud->validated());

        #Devolvemos el veterinario
        return response()->json($veterinario);
    }

    public function eliminar(Veterinario $veterinario)
    {
        #Obtenemos el usuario asociado al veterinario
        $usuario = $veterinario->usuario;

        #Eliminamos el veterinario
        $veterinario->delete();

        #Si existe el usuario, lo eliminamos
        if ($usuario) {
            $usuario->delete();
        }

        return response()->json(['mensaje' => 'Veterinario eliminado correctamente']);
    }
}

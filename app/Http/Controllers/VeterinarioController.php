<?php

namespace App\Http\Controllers;

use App\Http\Requests\ActualizarHorarioVeterinarioRequest;
use App\Http\Requests\ActualizarVeterinarioRequest;
use App\Http\Requests\GuardarVeterinarioRequest;
use App\Models\Especialidad;
use App\Models\Sucursal;
use App\Models\User;
use App\Models\Veterinario;
use App\Traits\HandlesPhotoUploads;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;

class VeterinarioController extends Controller
{
    use HandlesPhotoUploads;

    public function listado(Request $request)
    {
        // Obtenemos todos los veterinarios con sus relaciones y filtramos
        $query = Veterinario::with(['usuario', 'sucursal', 'especialidad'])
            ->when($request->filled('nombre'), fn($q) => $q->whereHas('usuario', fn($u) => $u->where('name', 'like', '%' . $request->nombre . '%')))
            ->when($request->filled('especialidad_id'), fn($q) => $q->where('especialidad_id', $request->especialidad_id))
            ->when($request->filled('sucursal_id'), fn($q) => $q->where('sucursal_id', $request->sucursal_id));

        // Obtenemos todos los veterinarios
        $veterinarios = $query->get();

        // Obtenemos todas las sucursales
        $sucursales = Cache::remember('sucursales_simple', now()->addMinutes(30), function () {
            return Sucursal::all();
        });

        // Obtenemos todas las especialidades
        $especialidades = Cache::remember('especialidades_simple', now()->addMinutes(30), function () {
            return Especialidad::all();
        });

        // Si la peticion es JSON
        if (request()->wantsJson()) {
            return response()->json([
                'veterinarios' => $veterinarios,
            ]);
        }

        // Devolvemos la vista con las sucursales, especialidades y veterinarios
        return Inertia::render('Veterinario/Listado', [
            'veterinarios' => $veterinarios,
            'sucursales' => $sucursales,
            'especialidades' => $especialidades,
        ]);
    }

    public function detalle(Veterinario $veterinario)
    {
        // Cargamos las relaciones necesarias para el detalle
        $veterinario = Veterinario::with(['usuario', 'sucursal', 'especialidad'])->findOrFail($veterinario->id);

        $citasRealizadas = $veterinario->citas()
            ->where('estado', 'completada')
            ->count();

        $citasPendientes = $veterinario->citas()
            ->where('estado', 'pendiente')
            ->count();

        $citasCanceladas = $veterinario->citas()
            ->where('estado', 'cancelada')
            ->count();

        // Obtenemos los bloqueos del veterinario ordenados por fecha de inicio descendente con sus relaciones
        $bloqueos = $veterinario->bloqueos()
            ->with(['especialidad', 'sucursal'])
            ->orderBy('fecha_inicio', 'desc')
            ->orderBy('hora_inicio', 'desc')
            ->get();

        // Devolvemos la vista con los datos
        return Inertia::render('Veterinario/Detalle', [
            'veterinario' => $veterinario,
            'bloqueos' => $bloqueos,
            'citasRealizadas' => $citasRealizadas,
            'citasPendientes' => $citasPendientes,
            'citasCanceladas' => $citasCanceladas,
            'sucursales' => Sucursal::orderBy('nombre')->get(),
            'especialidades' => Especialidad::orderBy('nombre')->get(),
        ]);
    }

    public function obtenerTodas()
    {
        // Obtenemos todos los veterinarios
        return Cache::remember('veterinarios_full', now()->addMinutes(30), function () {
            return Veterinario::with(['usuario', 'sucursal', 'especialidad'])->get();
        });
    }

    public function crear(GuardarVeterinarioRequest $solicitud)
    {
        // Obtenemos los datos validados
        $data = $solicitud->validated();

        // Creamos el usuario
        $usuario = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'rol_id' => 2,
        ]);

        // Procesamos la foto
        $fotoUrl = $this->procesarFoto($solicitud, 'foto', 'veterinarios/fotos');

        // Creamos el veterinario
        $veterinario = Veterinario::create([
            'user_id' => $usuario->id,
            'especialidad_id' => $data['especialidad_id'],
            'foto_perfil_url' => $fotoUrl ?? ($data['foto_perfil_url'] ?? null),
            'sucursal_id' => $data['sucursal_id'],
            'telefono' => $data['telefono'] ?? null,
            'direccion' => $data['direccion'] ?? null,
        ]);

        return response()->json($veterinario, 201);
    }

    public function actualizar(ActualizarVeterinarioRequest $solicitud, Veterinario $veterinario)
    {
        // Obtenemos los datos validados
        $data = $solicitud->validated();

        if ($solicitud->hasFile('foto')) {
            $data['foto_perfil_url'] = $this->procesarFoto($solicitud, 'foto', 'veterinarios/fotos', $veterinario->foto_perfil_url);
        }

        // Actualizamos el veterinario
        $veterinario->update($data);

        // Devolvemos el veterinario
        return response()->json($veterinario);
    }

    public function eliminar(Veterinario $veterinario)
    {
        // Obtenemos el usuario asociado al veterinario
        $usuario = $veterinario->usuario;

        // Eliminamos la foto física del storage
        $this->eliminarFotoFisica($veterinario->foto_perfil_url);

        // Eliminamos el veterinario
        $veterinario->delete();

        // Si existe el usuario, lo eliminamos
        if ($usuario) {
            $usuario->delete();
        }

        return response()->json(['mensaje' => 'Veterinario eliminado correctamente']);
    }

    public function actualizarHorario(ActualizarHorarioVeterinarioRequest $request, Veterinario $veterinario)
    {
        // Verificar que el usuario sea Admin o el propio veterinario
        if (auth()->user()->rol->nombre_interno !== 'admin' && auth()->user()->id !== $veterinario->user_id) {
            abort(403, 'No autorizado');
        }

        $veterinario->update([
            'horario' => $request->validated()['horario'],
        ]);

        // Limpiamos la caché relacionada con veterinarios
        Cache::forget('veterinarios_full');
        Cache::forget('veterinarios_simple');

        return response()->json(['mensaje' => 'Horario actualizado correctamente.']);
    }
}

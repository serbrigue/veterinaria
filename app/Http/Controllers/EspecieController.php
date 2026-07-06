<?php

namespace App\Http\Controllers;

use App\Http\Requests\ActualizarEspecieRequest;
use App\Http\Requests\GuardarEspecieRequest;
use App\Models\Especie;
use App\Traits\HandlesPhotoUploads;
use Illuminate\Http\Request;
use Inertia\Inertia;

class EspecieController extends Controller
{
    use HandlesPhotoUploads;

    public function listado(Request $request)
    {

        // Iniciamos la consulta
        $query = Especie::query();

        // Filtramos por texto
        if ($request->filled('texto')) {
            $query->where('nombre', 'like', '%'.$request->texto.'%');
        }

        // Las ordenamos por nombre
        $especies = $query->orderBy('nombre')->get();

        // Si la petición es JSON
        if ($request->wantsJson()) {
            return response()->json([
                'especies' => $especies,
            ]);
        }

        // Devolvemos la vista con las especies
        return Inertia::render('Especie/Listado', [
            'especies' => $especies,
        ]);
    }

    public function obtenerTodas()
    {
        // Obtenemos todas las especies ordenadas por nombre
        $especies = Especie::orderBy('nombre')->get();

        // Si la petición es JSON
        if (request()->wantsJson()) {
            return response()->json([
                'especies' => $especies,
            ]);
        }

        // Devolvemos las especies
        return $especies;
    }

    public function crear(GuardarEspecieRequest $solicitud)
    {
        // Validamos la solicitud
        $data = $solicitud->validated();

        // Guardamos el ID del usuario logueado en creado_por
        $data['creado_por'] = auth()->id();

        if ($solicitud->hasFile('foto')) {
            $data['imagen_url'] = $this->procesarFoto($solicitud, 'foto', 'especies/fotos');
        }

        // Creamos la especie
        $especie = Especie::create($data);

        return response()->json($especie, 201);
    }

    public function actualizar(ActualizarEspecieRequest $solicitud, Especie $especie)
    {
        // Actualizamos la especie
        $data = $solicitud->validated();

        if ($solicitud->hasFile('foto')) {
            $data['imagen_url'] = $this->procesarFoto($solicitud, 'foto', 'especies/fotos', $especie->imagen_url);
        }

        $especie->update($data);

        // Devolvemos la especie
        return response()->json($especie);
    }

    public function eliminar(Especie $especie)
    {
        // Eliminamos la foto física del storage
        $this->eliminarFotoFisica($especie->imagen_url);

        // Eliminamos la especie
        $especie->delete();

        // Devolvemos un mensaje de éxito
        return response()->json(['mensaje' => 'Especie eliminada correctamente']);
    }

    public function detalle(Especie $especie)
    {
        // Cargamos las razas de la especie y devolvemos la vista
        return Inertia::render('Especie/Detalle', [
            'especie' => $especie,
            'razas' => $especie->razas,
        ]);
    }
}

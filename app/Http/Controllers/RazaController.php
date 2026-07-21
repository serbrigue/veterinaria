<?php

namespace App\Http\Controllers;

use App\Http\Requests\ActualizarRazaRequest;
use App\Http\Requests\GuardarRazaRequest;
use App\Models\Especie;
use App\Models\Raza;
use App\Traits\HandlesPhotoUploads;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;

class RazaController extends Controller
{
    use HandlesPhotoUploads;

    public function listado(Request $request)
    {

        // Obtenemos el filtro de especie
        $filtroEspecie = $request->input('especie_id');

        // Obtenemos todas las razas con su especie, excluyendo comodín
        $razasCached = Cache::remember('razas_full_filt', now()->addMinutes(30), function () {
            return Raza::where('id', '!=', 999)->with('especie')->get();
        });

        // Obtenemos todas las especies, excluyendo comodín
        $especiesCached = Cache::remember('especies_simple_filt', now()->addMinutes(30), function () {
            return Especie::where('id', '!=', 999)->get();
        });

        // Si la peticion es JSON
        if (request()->wantsJson()) {
            $razas = $razasCached;
            // Si hay filtro de especie
            if ($filtroEspecie) {
                // Filtramos las razas
                $razas = $razas->where('especie_id', $filtroEspecie)->values();
            }

            // Devolvemos las razas y las especies
            return response()->json([
                'razas' => $razas,
                'especies' => $especiesCached,
            ]);
        }

        // Devolvemos la vista con las razas y las especies
        return Inertia::render('Raza/Listado', [
            'razas' => $razasCached,
            'especies' => $especiesCached,
        ]);
    }

    public function obtenerTodas()
    {

        // Obtenemos todas las razas ordenadas por nombre, excluyendo comodín
        return Raza::where('id', '!=', 999)->orderBy('nombre')->get();
    }

    public function crear(GuardarRazaRequest $solicitud)
    {
        // Obtenemos los datos validados
        $data = $solicitud->validated();

        // Guardamos el creador
        $data['creado_por'] = auth()->id();

        if ($solicitud->hasFile('foto')) {
            $data['imagen_url'] = $this->procesarFoto($solicitud, 'foto', 'razas/fotos');
        }

        // Creamos la raza
        $raza = Raza::create($data);

        // Devolvemos la raza
        return response()->json($raza, 201);
    }

    public function actualizar(ActualizarRazaRequest $solicitud, Raza $raza)
    {
        // Obtenemos los datos validados
        $data = $solicitud->validated();

        if ($solicitud->hasFile('foto')) {
            $data['imagen_url'] = $this->procesarFoto($solicitud, 'foto', 'razas/fotos', $raza->imagen_url);
        }

        // Actualizamos la raza
        $raza->update($data);

        // Devolvemos la raza
        return response()->json($raza);
    }

    public function eliminar(Raza $raza)
    {
        // Eliminamos la foto física del storage
        $this->eliminarFotoFisica($raza->imagen_url);

        // Eliminamos la raza
        $raza->delete();

        // Devolvemos mensaje de éxito
        return response()->json(['mensaje' => 'Raza eliminada correctamente']);
    }

    public function detalle(Raza $raza)
    {

        // Devolvemos la vista con la raza y la especie
        return Inertia::render('Raza/Detalle', [
            'raza' => $raza,
            'especie' => $raza->especie,
        ]);
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Raza;
use App\Models\Especie;
use App\Http\Requests\GuardarRazaRequest;
use App\Http\Requests\ActualizarRazaRequest;
use Illuminate\Http\Request;

use Inertia\Inertia;
use Illuminate\Support\Facades\Cache;

class RazaController extends Controller
{

    public function listado(Request $request)
    {

        #Obtenemos el filtro de especie
        $filtroEspecie = $request->input('especie_id');

        #Obtenemos todas las razas con su especie, y lo cacheamos por 30 minutos
        $razasCached = Cache::remember('razas_full', now()->addMinutes(30), function () {
            return Raza::with('especie')->get();
        });

        #Obtenemos todas las especies, y lo cacheamos por 30 minutos
        $especiesCached = Cache::remember('especies_simple', now()->addMinutes(30), function () {
            return Especie::all();
        });

        #Si la peticion es JSON
        if (request()->wantsJson()) {
            $razas = $razasCached;
            #Si hay filtro de especie
            if ($filtroEspecie) {
                #Filtramos las razas
                $razas = $razas->where('especie_id', $filtroEspecie)->values();
            }

            #Devolvemos las razas y las especies
            return response()->json([
                'razas' => $razas,
                'especies' => $especiesCached,
            ]);
        }

        #Devolvemos la vista con las razas y las especies
        return Inertia::render('Raza/Listado', [
            'razas' => $razasCached,
            'especies' => $especiesCached,
        ]);
    }

    public function obtenerTodas()
    {

        #Obtenemos todas las razas ordenadas por nombre
        return Raza::orderBy('nombre')->get();
    }

    public function crear(GuardarRazaRequest $solicitud)
    {

        #Obtenemos los datos validados
        $data = $solicitud->validated();

        #Creamos la raza
        $raza = Raza::create($data);

        #Devolvemos la raza
        return response()->json($raza, 201);
    }

    public function actualizar(ActualizarRazaRequest $solicitud, Raza $raza)
    {

        #Actualizamos la raza
        $raza->update($solicitud->validated());

        #Devolvemos la raza
        return response()->json($raza);
    }

    public function eliminar(Raza $raza)
    {

        #Eliminamos la raza
        $raza->delete();

        #Devolvemos mensaje de éxito
        return response()->json(['mensaje' => 'Raza eliminada correctamente']);
    }

    public function detalle(Raza $raza)
    {

        #Obtenemos las citas de la raza
        $citas = $raza->citas();

        #Devolvemos la vista con la raza y la especie
        return Inertia::render('Raza/Detalle', [
            'raza' => $raza,
            'especie' => $raza->especie,
        ]);
    }
}

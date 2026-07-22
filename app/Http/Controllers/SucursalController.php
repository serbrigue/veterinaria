<?php

namespace App\Http\Controllers;

use App\Http\Requests\ActualizarSucursalRequest;
use App\Http\Requests\GuardarSucursalRequest;
use App\Models\Sucursal;
use App\Traits\HandlesPhotoUploads;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SucursalController extends Controller
{
    use HandlesPhotoUploads;
    public function listado(Request $request)
    {

        // Obtenemos todas las sucursales ordenadas por nombre
        $sucursales = Sucursal::orderBy('nombre')->get();

        // Si la petición es JSON
        if ($request->wantsJson()) {
            return response()->json([
                'sucursales' => $sucursales,
            ]);
        }

        // Devolvemos la vista con las sucursales
        return Inertia::render('Sucursal/Listado', [
            'sucursales' => $sucursales,
        ]);
    }

    public function obtenerTodas()
    {

        // Obtenemos todas las sucursales ordenadas por nombre con veterinarios y boxes
        $sucursales = Sucursal::with(['veterinarios.usuario', 'boxes'])->orderBy('nombre')->get();

        // Si la petición es JSON
        if (request()->wantsJson()) {
            return response()->json([
                'sucursales' => $sucursales,
            ]);
        }

        return $sucursales;
    }

    public function crear(GuardarSucursalRequest $solicitud)
    {

        // Obtenemos los datos validados
        $data = $solicitud->validated();

        if ($solicitud->hasFile('imagen_url')) {
            $data['imagen_url'] = $this->procesarFoto($solicitud, 'imagen_url', 'sucursales/fotos');
        }

        // Creamos la sucursal
        $sucursal = Sucursal::create($data);

        // Devolvemos la sucursal

        return response()->json($sucursal, 201);
    }

    public function actualizar(ActualizarSucursalRequest $solicitud, Sucursal $sucursal)
    {

        // Obtenemos los datos validados
        $data = $solicitud->validated();

        if ($solicitud->hasFile('imagen_url')) {
            $data['imagen_url'] = $this->procesarFoto($solicitud, 'imagen_url', 'sucursales/fotos', $sucursal->imagen_url);
        }

        $sucursal->update($data);

        // Devolvemos la sucursal
        return response()->json($sucursal);
    }

    public function eliminar(Sucursal $sucursal)
    {
        $this->eliminarFotoFisica($sucursal->imagen_url);

        // Eliminamos la sucursal
        $sucursal->delete();

        // Devolvemos mensaje de éxito
        return response()->json(['mensaje' => 'Sucursal eliminada correctamente']);
    }

    public function detalle(Sucursal $sucursal)
    {

        // Obtenemos los veterinarios y boxes de la sucursal
        $veterinarios = $sucursal->veterinarios;
        $boxes = $sucursal->boxes;

        // Si la petición es JSON
        if (request()->wantsJson()) {
            return response()->json([
                'sucursal' => $sucursal,
                'veterinarios' => $veterinarios,
                'boxes' => $boxes,
            ]);
        }

        // Devolvemos la vista con la sucursal, veterinarios y boxes
        return Inertia::render('Sucursal/Detalle', [
            'sucursal' => $sucursal,
            'veterinarios' => $veterinarios,
            'boxes' => $boxes,
        ]);
    }
}

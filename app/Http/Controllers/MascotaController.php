<?php

namespace App\Http\Controllers;

use App\Models\Mascota;
use App\Models\Cliente;
use App\Models\Cita;
use App\Http\Requests\GuardarMascotaRequest;
use App\Http\Requests\ActualizarMascotaRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Inertia\Inertia;

class MascotaController extends Controller
{
    /**
     * EJEMPLO DE REFERENCIA:
     * Este controlador tiene el CRUD completo en español.
     * Úsalo como guía para crear los demás controladores.
     */

    public function listado(Request $request)
    {

        if (auth()->user()->isAdmin() || auth()->user()->isVeterinario()) {
            $mascotas = Mascota::with('cliente.usuario', 'raza.especie')->get();
            $clientes = Cliente::with('usuario')->get();
        } else {
            $mascotas = Mascota::with('cliente.usuario', 'raza.especie')
                ->where('cliente_id', auth()->user()->cliente?->id)
                ->get();
            $clientes = Cliente::where('user_id', auth()->id())->with('usuario')->get();
        }

        if ($request->wantsJson()) {
            return response()->json([
                'mascotas' => $mascotas,
                'clientes' => $clientes,
            ]);
        }

        return Inertia::render('Mascota/Listado', [
            'mascotas' => $mascotas,
            'clientes' => $clientes,
            'consultadoEn' => Carbon::now()->format('d/m/Y H:i'),
        ]);
    }

    public function obtenerTodas()
    {
        return Mascota::where('cliente_id', auth()->user()->cliente?->id)->get();
    }

    public function crear(GuardarMascotaRequest $solicitud)
    {
        $data = $solicitud->validated();

        // Si es cliente, forzamos que la mascota se le asocie a él mismo en la base de datos
        if (auth()->user()->isCliente()) {
            $data['cliente_id'] = auth()->user()->cliente->id;
        }

        $mascota = Mascota::create($data);

        return response()->json($mascota, 201);
    }

    public function actualizar(ActualizarMascotaRequest $solicitud, Mascota $mascota)
    {
        $mascota->update($solicitud->validated());

        return response()->json($mascota);
    }

    public function eliminar(Mascota $mascota)
    {
        $mascota->delete();

        return response()->json(['mensaje' => 'Mascota eliminada correctamente']);
    }

    public function detalle(Mascota $mascota)
    {

        $proximasCitas = Cita::with('veterinario.usuario', 'box.sucursal')->where('mascota_id', $mascota->id)->where('fecha_hora', '>=', Carbon::now())->where('estado', '!=', 'cancelada')->get();
        $historialClinico = Cita::with('veterinario.usuario', 'box.sucursal')->where('mascota_id', $mascota->id)->where('estado', '=', 'completada')->orderBy('fecha_hora', 'desc')->get();



        $mascota = Mascota::with('cliente.usuario', 'raza.especie')->find($mascota->id);

        return Inertia::render('Mascota/Detalle', [
            'mascota' => $mascota,
            'proximasCitas' => $proximasCitas,
            'historialClinico' => $historialClinico,
            'cliente' => $mascota->cliente->usuario,
            'especie' => $mascota->raza->especie,
            'raza' => $mascota->raza,
        ]);
    }
}

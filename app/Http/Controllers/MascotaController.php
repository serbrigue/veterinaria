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

    public function listado(Request $request)
    {
        # Iniciamos la consulta con eager loading
        $query = Mascota::with('cliente.usuario', 'raza.especie')
            ->when($request->filled('nombre'), fn($q) => $q->where('nombre', 'like', '%' . $request->nombre . '%'))
            ->when($request->filled('especie_id'), fn($q) => $q->whereHas('raza', fn($r) => $r->where('especie_id', $request->especie_id)))
            ->when($request->filled('raza_id'), fn($q) => $q->where('raza_id', $request->raza_id))
            ->when($request->filled('sexo'), fn($q) => $q->where('sexo', $request->sexo))
            ->when($request->filled('esterilizado'), fn($q) => $q->where('esterilizado', $request->esterilizado));

        # Obtenemos todas las mascotas si el usuario es admin o veterinario
        if (auth()->user()->isAdmin() || auth()->user()->isVeterinario()) {
            $clientes = Cliente::with('usuario')->get();
        } else {
            # Si no, filtramos por el cliente
            $query->where('cliente_id', auth()->user()->cliente?->id);
            $clientes = Cliente::where('user_id', auth()->id())->with('usuario')->get();
        }

        # Obtenemos todas las mascotas paginadas
        $mascotas = $query->paginate(15);

        # Verificamos si la solicitud es JSON
        if ($request->wantsJson()) {
            return response()->json([
                'mascotas' => $mascotas,
                'clientes' => $clientes,
            ]);
        }

        # Devolvemos la vista
        return Inertia::render('Mascota/Listado', [
            'mascotas' => $mascotas,
            'clientes' => $clientes,
            'consultadoEn' => Carbon::now()->format('d/m/Y H:i'),
        ]);
    }

    public function obtenerTodas()
    {
        # Obtenemos todas las mascotas paginadas
        return Mascota::where('cliente_id', auth()->user()->cliente?->id)->get();
    }

    public function crear(GuardarMascotaRequest $solicitud)
    {
        # Obtenemos los datos validados
        $data = $solicitud->validated();

        # Si es cliente, forzamos que la mascota se le asocie a él mismo en la base de datos
        if (auth()->user()->isCliente()) {
            $data['cliente_id'] = auth()->user()->cliente->id;
        }

        # Creamos la mascota
        $mascota = Mascota::create($data);

        # Devolvemos la mascota
        return response()->json($mascota, 201);
    }

    public function actualizar(ActualizarMascotaRequest $solicitud, Mascota $mascota)
    {
        # Actualizamos la mascota
        $mascota->update($solicitud->validated());

        # Devolvemos la mascota
        return response()->json($mascota);
    }

    public function eliminar(Mascota $mascota)
    {
        # Eliminamos la mascota
        $mascota->delete();

        # Devolvemos mensaje de éxito
        return response()->json(['mensaje' => 'Mascota eliminada correctamente']);
    }

    public function detalle(Mascota $mascota)
    {
        # Obtenemos las próximas citas (pendientes)
        $proximasCitas = Cita::with('veterinario.usuario', 'box.sucursal')->where('mascota_id', $mascota->id)->where('fecha_hora', '>=', Carbon::now())->where('estado', '=', 'pendiente')->get();
        # Obtenemos el historial clínico (citas pasadas)
        $historialClinico = Cita::with('veterinario.usuario', 'box.sucursal')->where('mascota_id', $mascota->id)->where('estado', '=', 'completada')->orderBy('fecha_hora', 'desc')->paginate(5);



        # Obtenemos la mascota
        $mascota = Mascota::with('cliente.usuario', 'raza.especie')->find($mascota->id);

        # Devolvemos la vista
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

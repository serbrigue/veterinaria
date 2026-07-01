<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\Sucursal;
use App\Http\Requests\GuardarClienteRequest;
use App\Mail\NotificacionMasivaMail;
use Illuminate\Support\Facades\Mail;
use App\Http\Requests\ActualizarClienteRequest;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ClienteController extends Controller
{

    public function listado(Request $request)
    {
        # Obtenemos los datos del query
        $query = Cliente::with(['usuario', 'mascotas']);

        # Verificamos si el usuario es administrador o veterinario
        if (auth()->user()->isAdmin() || auth()->user()->isVeterinario()) {
            # Los administradores y veterinarios ven todos, con opción a filtrar
        } else {
            # Un cliente solo ve su perfil
            $query->where('user_id', auth()->id());
        }

        # Filtros
        $query->when(
            $request->filled('nombre'),
            fn($q) =>
            $q->whereHas('usuario', fn($u) => $u->where('name', 'like', '%' . $request->nombre . '%'))
        )
            ->when(
                $request->filled('mascota'),
                fn($q) =>
                $q->whereHas('mascotas', fn($m) => $m->where('nombre', 'like', '%' . $request->mascota . '%'))
            )
            ->when(
                $request->filled('sucursal_id'),
                fn($q) =>
                $q->whereHas('mascotas.citas.box', fn($b) => $b->where('sucursal_id', $request->sucursal_id))
            )
            ->when($request->filled('estado_pago'), function ($q) use ($request) {
                if ($request->estado_pago === 'moroso') {
                    $q->whereHas('transacciones', fn($t) => $t->where('estado', 'pendiente'));
                } elseif ($request->estado_pago === 'al_dia') {
                    $q->whereDoesntHave('transacciones', fn($t) => $t->where('estado', 'pendiente'));
                }
            });

        #Cargamos las transacciones pendientes explícitamente para mostrarlas rápido en el badge
        $query->with(['transacciones' => function ($q) {
            $q->where('estado', 'pendiente');
        }]);

        # Paginamos los resultados
        $clientes = $query->paginate(15);

        # Si la solicitud es en formato JSON, devolvemos JSON
        if ($request->wantsJson()) {
            return response()->json([
                'clientes' => $clientes,
            ]);
        }

        # Obtenemos las sucursales
        $sucursales = Sucursal::select('id', 'nombre')->get();

        # Devolvemos la vista
        return Inertia::render('Cliente/Listado', [
            'clientes' => $clientes,
            'sucursales' => $sucursales,
        ]);
    }

    public function detalle(Request $request, Cliente $cliente)
    {
        # Cargamos los datos del cliente con eager loading
        $cliente->load([
            'usuario',
            'mascotas.raza.especie'
        ]);

        # Obtenemos las transacciones
        $transacciones = $cliente->transacciones()
            ->with('cita.prestacion')
            ->orderByDesc('created_at')
            ->paginate(5);

        # Obtenemos la deuda total
        $deudaTotal = $cliente->transacciones()->where('estado', 'pendiente')->sum('monto_total');

        # Obtenemos el número de transacciones pendientes
        $transaccionesPendientesCount = $cliente->transacciones()->where('estado', 'pendiente')->count();

        # Devolvemos la vista
        return Inertia::render('Cliente/Detalle', [
            'cliente' => $cliente,
            'transacciones' => $transacciones,
            'deudaTotal' => $deudaTotal,
            'transaccionesPendientesCount' => $transaccionesPendientesCount
        ]);
    }

    public function obtenerTodas()
    {
        # Retorna todos los clientes
        return Cliente::where('user_id', auth()->id())->get();
    }

    public function crear(GuardarClienteRequest $solicitud)
    {
        # Crea un nuevo cliente
        $cliente = Cliente::create([
            'nombre' => $solicitud->nombre,
            'email' => $solicitud->email,
            'telefono' => $solicitud->telefono,
            'direccion' => $solicitud->direccion,
            'user_id' => auth()->id(),
        ]);

        return response()->json($cliente, 201);
    }

    public function actualizar(ActualizarClienteRequest $solicitud, Cliente $cliente)

    {
        # Actualizamos el cliente
        $cliente->update($solicitud->validated());

        return response()->json($cliente);
    }

    public function eliminar(Cliente $cliente)
    {
        # Elimina el cliente
        $cliente->delete();

        return response()->json(['mensaje' => 'Cliente eliminado correctamente']);
    }

    public function enviarCorreoMasivo(Request $request)
    {

        # Verificamos que el usuario sea administrador o veterinario
        if (!auth()->user()->isAdmin() && !auth()->user()->isVeterinario()) {
            return response()->json(['error' => 'No autorizado para realizar esta acción.'], 403);
        }

        # Validamos la solicitud
        $validated = $request->validate([
            'clientes_ids' => 'required|array',
            'clientes_ids.*' => 'exists:clientes,id',
            'asunto' => 'required|string|max:255',
            'mensaje' => 'required|string|max:2000',
        ]);

        # Obtenemos los clientes
        $clientes = Cliente::whereIn('id', $validated['clientes_ids'])->with('usuario')->get();

        # Enviamos los correos
        foreach ($clientes as $cliente) {
            $email = $cliente->usuario?->email;

            if ($email) {

                # Enviamos el correo a queue
                Mail::to($email)->send(
                    new NotificacionMasivaMail(
                        $validated['asunto'],
                        $validated['mensaje'],
                        $cliente->usuario->name
                    )
                );
            }
        }

        return response()->json(['mensaje' => 'Correos enviados correctamente a ' . $clientes->count() . ' clientes.']);
    }
}

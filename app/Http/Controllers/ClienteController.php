<?php

namespace App\Http\Controllers;

use App\Http\Requests\ActualizarClienteRequest;
use App\Http\Requests\GuardarClienteRequest;
use App\Http\Requests\GuardarCorreoMasivoRequest;
use App\Http\Requests\GuardarCorreoMoraRequest;
use App\Mail\NotificacionMasivaMail;
use App\Models\Cliente;
use App\Models\Sucursal;
use App\Models\User;
use App\Models\Rol;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;
use App\Mail\MoraPagoMail;

class ClienteController extends Controller
{
    public function listado(Request $request)
    {
        // Obtenemos los datos del query
        $query = Cliente::where('id', '!=', 999)->with(['usuario', 'mascotas']);

        // Verificamos si el usuario es administrador, veterinario o secretaria
        if (auth()->user()->isAdmin() || auth()->user()->isVeterinario() || auth()->user()->isSecretaria()) {
            // Los administradores, veterinarios y secretarias ven todos, con opción a filtrar
        } else {
            // Un cliente solo ve su perfil
            $query->where('user_id', auth()->id());
        }

        // Filtros
        $query->when(
            $request->filled('nombre'),
            fn($q) => $q->whereHas('usuario', fn($u) => $u->where('name', 'like', '%' . $request->nombre . '%'))
        )
            ->when(
                $request->filled('mascota'),
                fn($q) => $q->whereHas('mascotas', fn($m) => $m->where('nombre', 'like', '%' . $request->mascota . '%'))
            )
            ->when(
                $request->filled('sucursal_id'),
                fn($q) => $q->whereHas('mascotas.citas.box', fn($b) => $b->where('sucursal_id', $request->sucursal_id))
            )
            ->when($request->filled('estado_pago'), function ($q) use ($request) {
                if ($request->estado_pago === 'moroso') {
                    $q->whereHas('transacciones', fn($t) => $t->where('estado', 'pendiente'));
                } elseif ($request->estado_pago === 'al_dia') {
                    $q->whereDoesntHave('transacciones', fn($t) => $t->where('estado', 'pendiente'));
                }
            });

        // Cargamos las transacciones pendientes explícitamente para mostrarlas rápido en el badge
        $query->with(['transacciones' => function ($q) {
            $q->where('estado', 'pendiente');
        }]);

        // Paginamos los resultados
        $clientes = $query->paginate(15);

        // Si la solicitud es en formato JSON, devolvemos JSON
        if ($request->wantsJson()) {
            return response()->json([
                'clientes' => $clientes,
            ]);
        }

        // Obtenemos las sucursales
        $sucursales = Sucursal::select('id', 'nombre')->get();

        // Devolvemos la vista
        return Inertia::render('Cliente/Listado', [
            'clientes' => $clientes,
            'sucursales' => $sucursales,
        ]);
    }

    public function detalle(Request $request, Cliente $cliente)
    {
        // Cargamos los datos del cliente con eager loading
        $cliente->load([
            'usuario',
            'mascotas.raza.especie',
        ]);

        // Obtenemos las transacciones
        $transaccionesQuery = $cliente->transacciones()
            ->with('cita.prestacion');

        if ($request->filled('estado')) {
            $transaccionesQuery->where('estado', $request->estado);
        }

        $transacciones = $transaccionesQuery->orderByDesc('created_at')
            ->paginate(5)->withQueryString();

        // Obtenemos la deuda total
        $deudaTotal = $cliente->transacciones()->where('estado', 'pendiente')->sum('monto_total');

        // Obtenemos el número de transacciones pendientes
        $transaccionesPendientesCount = $cliente->transacciones()->where('estado', 'pendiente')->count();

        // Devolvemos la vista
        return Inertia::render('Cliente/Detalle', [
            'cliente' => $cliente,
            'transacciones' => $transacciones,
            'deudaTotal' => $deudaTotal,
            'transaccionesPendientesCount' => $transaccionesPendientesCount,
        ]);
    }

    public function obtenerTodas()
    {
        // Retorna todos los clientes
        if (auth()->user()->isAdmin() || auth()->user()->isVeterinario() || auth()->user()->isSecretaria()) {
            return Cliente::with('usuario')->get();
        }
        return Cliente::where('user_id', auth()->id())->get();
    }

    public function crear(GuardarClienteRequest $solicitud)
    {
        $rolCliente = Rol::where('nombre_interno', 'cliente')->first();

        // Crear el usuario asociado
        $usuario = User::create([
            'name' => $solicitud->nombre,
            'email' => $solicitud->email,
            'password' => \Illuminate\Support\Facades\Hash::make('password123'),
            'rol_id' => $rolCliente?->id,
        ]);

        // Crear el cliente asociado a ese usuario
        $cliente = Cliente::create([
            'telefono' => $solicitud->telefono,
            'direccion' => $solicitud->direccion,
            'user_id' => $usuario->id,
        ]);

        return response()->json($cliente->load('usuario'), 201);
    }

    public function actualizar(ActualizarClienteRequest $solicitud, Cliente $cliente)
    {
        // Actualizar el usuario asociado
        if ($cliente->usuario) {
            $cliente->usuario->update([
                'name' => $solicitud->nombre,
                'email' => $solicitud->email,
            ]);
        }

        // Actualizar el cliente
        $cliente->update([
            'telefono' => $solicitud->telefono,
            'direccion' => $solicitud->direccion,
        ]);

        return response()->json($cliente->load('usuario'));
    }

    public function eliminar(Cliente $cliente)
    {
        // Elimina el cliente
        $cliente->delete();

        return response()->json(['mensaje' => 'Cliente eliminado correctamente']);
    }

    public function enviarCorreoMasivo(GuardarCorreoMasivoRequest $request)
    {

        // Verificamos que el usuario sea administrador o secretaria
        if (! auth()->user()->isAdmin() || ! auth()->user()->isSecretaria()) {
            return response()->json(['error' => 'No autorizado para realizar esta acción.'], 403);
        }

        // Validamos la solicitud
        $validated = $request->validated();

        // Obtenemos los clientes
        $clientes = Cliente::whereIn('id', $validated['clientes_ids'])->with('usuario')->get();

        // Enviamos los correos
        foreach ($clientes as $cliente) {
            $email = $cliente->usuario?->email;

            if ($email) {

                // Enviamos el correo a queue
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

    public function enviarCorreoMora(GuardarCorreoMoraRequest $request, Cliente $cliente)
    {
        // Verificamos que el usuario sea administrador o secretaria
        if (! auth()->user()->isAdmin() && ! auth()->user()->isSecretaria()) {
            return response()->json(['error' => 'No autorizado para realizar esta acción.'], 403);
        }

        // Validamos la solicitud
        $validated = $request->validated();

        // Obtenemos las transacciones seleccionadas
        $transacciones = $cliente->transacciones()->whereIn('id', $validated['transacciones_ids'])->with('cita')->get();

        $email = $cliente->usuario?->email;
        if ($email) {
            // Enviamos el correo
            Mail::to($email)->send(new MoraPagoMail($cliente, $transacciones));
        }

        return response()->json(['mensaje' => 'Correo de mora de pago enviado correctamente.']);
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\Sucursal;
use App\Http\Requests\GuardarClienteRequest;
use App\Http\Requests\ActualizarClienteRequest;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ClienteController extends Controller
{
    /**
     * MÓDULO 4 — Backend de referencia (no modificar).
     * Tu trabajo: migración, rutas web/api y Vue (axios).
     */
    public function listado(Request $request)
    {
        $query = Cliente::with(['usuario', 'mascotas']);

        if (auth()->user()->isAdmin() || auth()->user()->isVeterinario()) {
            // Administradores y veterinarios ven todos, con opción a filtrar
        } else {
            // Un cliente solo ve su perfil
            $query->where('user_id', auth()->id());
        }

        // Filtros usando when() para mantener consistencia con otros controladores
        $query->when($request->filled('nombre'), fn($q) => 
            $q->whereHas('usuario', fn($u) => $u->where('name', 'like', '%' . $request->nombre . '%'))
        )
        ->when($request->filled('mascota'), fn($q) => 
            $q->whereHas('mascotas', fn($m) => $m->where('nombre', 'like', '%' . $request->mascota . '%'))
        )
        ->when($request->filled('sucursal_id'), fn($q) => 
            $q->whereHas('mascotas.citas.box', fn($b) => $b->where('sucursal_id', $request->sucursal_id))
        )
        ->when($request->filled('estado_pago'), function($q) use ($request) {
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

        $clientes = $query->paginate(15);

        if ($request->wantsJson()) {
            return response()->json([
                'clientes' => $clientes,
            ]);
        }

        $sucursales = Sucursal::select('id', 'nombre')->get();

        return Inertia::render('Cliente/Listado', [
            'clientes' => $clientes,
            'sucursales' => $sucursales,
        ]);
    }

    public function detalle(Request $request, Cliente $cliente)
    {
        $this->authorize('ver', $cliente);

        $cliente->load([
            'usuario',
            'mascotas.raza.especie'
        ]);

        $transacciones = $cliente->transacciones()
            ->with('cita.prestacion')
            ->orderByDesc('created_at')
            ->paginate(5);

        $deudaTotal = $cliente->transacciones()->where('estado', 'pendiente')->sum('monto_total');
        $transaccionesPendientesCount = $cliente->transacciones()->where('estado', 'pendiente')->count();

        return Inertia::render('Cliente/Detalle', [
            'cliente' => $cliente,
            'transacciones' => $transacciones,
            'deudaTotal' => $deudaTotal,
            'transaccionesPendientesCount' => $transaccionesPendientesCount
        ]);
    }

    public function obtenerTodas()
    {
        return Cliente::where('user_id', auth()->id())->get();
    }

    public function crear(GuardarClienteRequest $solicitud)
    {
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
        if ($cliente->user_id !== auth()->id()) {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        $cliente->update($solicitud->validated());

        return response()->json($cliente);
    }

    public function eliminar(Cliente $cliente)
    {
        if ($cliente->user_id !== auth()->id()) {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        $cliente->delete();

        return response()->json(['mensaje' => 'Cliente eliminado correctamente']);
    }
}

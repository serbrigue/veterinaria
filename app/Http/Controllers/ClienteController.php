<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
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

    public function listado()
    {
        $clientes = Cliente::where('user_id', auth()->id())->get();

        return Inertia::render('Cliente/Listado', [
            'clientes' => $clientes,
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

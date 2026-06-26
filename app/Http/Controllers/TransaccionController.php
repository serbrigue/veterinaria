<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Transaccion;
use App\Models\Sucursal;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;
use App\Mail\PagoConfirmadoMail;
use Inertia\Inertia;
use Illuminate\Support\Facades\Cache;

class TransaccionController extends Controller
{
    /**
     * Listado detallado de ingresos filtrables (BI Dashboard link).
     */
    public function listado(Request $request)
    {
        // Esta vista es típicamente para administradores, asumiremos que si llega acá tiene permiso
        // o se puede proteger con middleware o policies adicionales en el router.

        $query = Transaccion::with([
            'cita.veterinario.usuario',
            'cita.box.sucursal',
            'cliente.usuario',
            'cita.mascota'
        ])->where('estado', 'pagado');

        // Filtro: Mes y Año por separado
        if ($request->filled('anio')) {
            $query->whereYear('fecha_pago', $request->anio);
        }

        if ($request->filled('mes')) {
            $query->whereMonth('fecha_pago', $request->mes);
        }

        // Filtro: Sucursal
        if ($request->filled('sucursal_id')) {
            $sucursalId = $request->sucursal_id;
            $query->whereHas('cita.box', function ($q) use ($sucursalId) {
                $q->where('sucursal_id', $sucursalId);
            });
        }

        $totalFiltrado = (clone $query)->sum('monto_pagado');
        $transacciones = $query->orderByDesc('fecha_pago')->paginate(15);

        if ($request->wantsJson()) {
            return response()->json([
                'transacciones' => $transacciones,
                'totalFiltrado' => $totalFiltrado
            ]);
        }

        return Inertia::render('Transaccion/Listado', [
            'transacciones_iniciales' => $transacciones,
            'total_filtrado_inicial' => $totalFiltrado,
            'sucursales' => Cache::remember('sucursales_simple', now()->addMinutes(30), function () {
                return Sucursal::all();
            })
        ]);
    }

    /**
     * Render the mock checkout page for a pending transaction.
     */
    public function checkout(Transaccion $transaccion)
    {
        if ($transaccion->estado !== 'pendiente') {
            return redirect()->route('citas.detalle', $transaccion->cita_id)
                ->with('error', 'Esta transacción ya no se encuentra pendiente.');
        }

        // Load necessary relations for the checkout summary
        $transaccion->load([
            'cita.prestacion',
            'cita.mascota',
            'cliente.usuario'
        ]);

        return Inertia::render('Transaccion/Checkout', [
            'transaccion' => $transaccion
        ]);
    }

    /**
     * Process the mock payment.
     */
    public function procesarPago(Request $request, Transaccion $transaccion)
    {
        $request->validate([
            'metodo_pago' => 'required|string|in:tarjeta,efectivo,transferencia',
        ]);

        if ($transaccion->estado !== 'pendiente') {
            return response()->json(['error' => 'La transacción no está pendiente.'], 400);
        }

        $transaccion->update([
            'estado' => 'pagado',
            'metodo_pago' => $request->metodo_pago,
            'monto_pagado' => $transaccion->monto_total,
            'fecha_pago' => Carbon::now(),
        ]);

        $transaccion->loadMissing(['cliente.usuario', 'cita.mascota', 'cita.prestacion']);
        $clienteEmail = $transaccion->cliente->usuario->email ?? null;
        if ($clienteEmail) {
            Mail::to($clienteEmail)->send(new PagoConfirmadoMail($transaccion));
        }

        return response()->json(['message' => 'Pago procesado con éxito.', 'transaccion' => $transaccion]);
    }
}

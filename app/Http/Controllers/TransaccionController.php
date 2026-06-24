<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Transaccion;
use Illuminate\Support\Carbon;
use Inertia\Inertia;

class TransaccionController extends Controller
{
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

        return response()->json(['message' => 'Pago procesado con éxito.', 'transaccion' => $transaccion]);
    }
}

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

        #Obtenemos todas las transacciones
        $query = Transaccion::with([
            'cita.veterinario.usuario',
            'cita.box.sucursal',
            'cliente.usuario',
            'cita.mascota'
        ])->where('estado', 'pagado')->when($request->filled('anio'), function ($query) use ($request) {
            return $query->whereYear('fecha_pago', $request->anio);
        })->when($request->filled('mes'), function ($query) use ($request) {
            return $query->whereMonth('fecha_pago', $request->mes);
        })->when($request->filled('sucursal_id'), function ($query) use ($request) {
            return $query->whereHas('cita.box', function ($q) use ($request) {
                $q->where('sucursal_id', $request->sucursal_id);
            });
        });

        #Obtenemos el total filtrado
        $totalFiltrado = (clone $query)->sum('monto_pagado');

        #Obtenemos las transacciones ordenadas por fecha de pago
        $transacciones = $query->orderByDesc('fecha_pago')->paginate(15);

        #Si la petición es JSON
        if ($request->wantsJson()) {
            return response()->json([
                'transacciones' => $transacciones,
                'totalFiltrado' => $totalFiltrado
            ]);
        }

        #Devolvemos la vista con las transacciones, total filtrado y sucursales
        return Inertia::render('Transaccion/Listado', [
            'transacciones_iniciales' => $transacciones,
            'total_filtrado_inicial' => $totalFiltrado,
            'sucursales' => Cache::remember('sucursales_simple', now()->addMinutes(30), function () {
                return Sucursal::all();
            })
        ]);
    }


    public function checkout(Transaccion $transaccion)
    {
        #Si la transacción no está pendiente, redirigimos al detalle de la cita
        if ($transaccion->estado !== 'pendiente') {
            return redirect()->route('citas.detalle', $transaccion->cita_id)
                ->with('error', 'Esta transacción ya no se encuentra pendiente.');
        }

        #Cargamos las relaciones necesarias para el resumen de checkout
        $transaccion->load([
            'cita.prestacion',
            'cita.mascota',
            'cliente.usuario'
        ]);

        #Devolvemos la vista con los datos
        return Inertia::render('Transaccion/Checkout', [
            'transaccion' => $transaccion
        ]);
    }


    public function procesarPago(Request $request, Transaccion $transaccion)
    {
        #Validamos la solicitud
        $request->validate([
            'metodo_pago' => 'required|string|in:tarjeta,efectivo,transferencia',
        ]);

        #Si la transacción no está pendiente, devolvemos error
        if ($transaccion->estado !== 'pendiente') {
            return response()->json(['error' => 'La transacción no está pendiente.'], 400);
        }

        #Actualizamos la transacción
        $transaccion->update([
            'estado' => 'pagado',
            'metodo_pago' => $request->metodo_pago,
            'monto_pagado' => $transaccion->monto_total,
            'fecha_pago' => Carbon::now(),
        ]);

        #Cargamos las relaciones necesarias para el correo de confirmación de pago
        $transaccion->loadMissing(['cliente.usuario', 'cita.mascota', 'cita.prestacion']);

        #Obtenemos el correo del cliente
        $clienteEmail = $transaccion->cliente->usuario->email ?? null;

        #Enviamos el correo de confirmación de pago
        if ($clienteEmail) {
            Mail::to($clienteEmail)->send(new PagoConfirmadoMail($transaccion));
        }

        #Devolvemos la respuesta JSON
        return response()->json(['message' => 'Pago procesado con éxito.', 'transaccion' => $transaccion]);
    }
}

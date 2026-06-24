<?php

namespace App\Http\Controllers;

use App\Models\CitaCargo;
use App\Models\Cita;
use App\Models\Prestacion;
use App\Models\Insumo;
use App\Http\Requests\GuardarCitaCargoRequest;
use App\Http\Requests\ActualizarCitaCargoRequest;

class CitaCargoController extends Controller
{

    public function crear(GuardarCitaCargoRequest $solicitud)
    {
        $data = $solicitud->validated();

        $insumo = Insumo::findOrFail($data['insumo_id']);
        if ($insumo->stock_actual < $data['cantidad']) {
            return response()->json(['error' => 'Stock insuficiente para el insumo.'], 422);
        }

        $insumo->decrement('stock_actual', $data['cantidad']);

        $precioUnitario = $insumo->precio_venta;
        $subtotal = $precioUnitario * $data['cantidad'];

        $prestacion = Cita::where('id', $data['cita_id'])->first()->prestacion_id;

        $cargo = CitaCargo::create([
            'cita_id' => $data['cita_id'],
            'insumo_id' => $insumo->id,
            'prestacion_id' => $prestacion,
            'cantidad' => $data['cantidad'],
            'precio_unitario' => $precioUnitario,
            'subtotal' => $subtotal,
            'pago_vet' => 0, // Los insumos no generan comisión al veterinario
        ]);

        // Retornamos el cargo creado con su relación para que Vue lo muestre al instante
        return response()->json($cargo->load('insumo'), 201);
    }


    public function actualizar(ActualizarCitaCargoRequest $solicitud, CitaCargo $cargo)
    {
        $data = $solicitud->validated();
        $nuevaCantidad = $data['cantidad'];
        $diferencia = $nuevaCantidad - $cargo->cantidad;

        $insumo = Insumo::findOrFail($cargo->insumo_id);

        if ($diferencia > 0 && $insumo->stock_actual < $diferencia) {
            return response()->json(['error' => 'Stock insuficiente para aumentar la cantidad del insumo.'], 422);
        }

        if ($diferencia > 0) {
            $insumo->decrement('stock_actual', $diferencia);
        } elseif ($diferencia < 0) {
            $insumo->increment('stock_actual', abs($diferencia));
        }

        // Recalculamos el subtotal manteniendo el precio histórico de cuando se añadió el insumo
        $nuevoSubtotal = $cargo->precio_unitario * $nuevaCantidad;

        $cargo->update([
            'cantidad' => $nuevaCantidad,
            'subtotal' => $nuevoSubtotal,
        ]);

        return response()->json($cargo->load('insumo'));
    }

    public function eliminar(CitaCargo $cargo)
    {
        $insumo = Insumo::findOrFail($cargo->insumo_id);
        $insumo->increment('stock_actual', $cargo->cantidad);

        $cargo->delete();
        return response()->json(['mensaje' => 'Cargo eliminado correctamente de la cuenta']);
    }
}

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
        # Validamos la solicitud
        $data = $solicitud->validated();

        # Buscamos el insumo y verificamos su stock
        $insumo = Insumo::findOrFail($data['insumo_id']);

        # Si no hay stock suficiente, devolvemos error
        if ($insumo->stock_actual < $data['cantidad']) {
            return response()->json(['error' => 'Stock insuficiente para el insumo.'], 422);
        }

        # Descontamos el stock del insumo
        $insumo->decrement('stock_actual', $data['cantidad']);

        # Obtenemos el precio unitario y calculamos el subtotal
        $precioUnitario = $insumo->precio_venta;
        $subtotal = $precioUnitario * $data['cantidad'];

        # Obtenemos la prestacion de la cita
        $prestacion = Cita::where('id', $data['cita_id'])->first()->prestacion_id;

        # Creamos el cargo
        $cargo = CitaCargo::create([
            'cita_id' => $data['cita_id'],
            'insumo_id' => $insumo->id,
            'prestacion_id' => $prestacion,
            'cantidad' => $data['cantidad'],
            'precio_unitario' => $precioUnitario,
            'subtotal' => $subtotal,
            'pago_vet' => 0,
        ]);

        # Retornamos el cargo creado con su relación para que Vue lo muestre al instante
        return response()->json($cargo->load('insumo'), 201);
    }


    public function actualizar(ActualizarCitaCargoRequest $solicitud, CitaCargo $cargo)
    {
        # Validamos la solicitud
        $data = $solicitud->validated();

        # Calculamos la diferencia entre la nueva cantidad y la cantidad actual
        $nuevaCantidad = $data['cantidad'];
        $diferencia = $nuevaCantidad - $cargo->cantidad;

        # Buscamos el insumo
        $insumo = Insumo::findOrFail($cargo->insumo_id);

        # Verificamos si hay stock suficiente para aumentar la cantidad del insumo
        if ($diferencia > 0 && $insumo->stock_actual < $diferencia) {
            return response()->json(['error' => 'Stock insuficiente para aumentar la cantidad del insumo.'], 422);
        }

        # Actualizamos el stock del insumo
        if ($diferencia > 0) {
            $insumo->decrement('stock_actual', $diferencia);

            # Si la cantidad es menor que 0, devolvemos el stock
        } elseif ($diferencia < 0) {
            $insumo->increment('stock_actual', abs($diferencia));
        }

        # Recalculamos el subtotal manteniendo el precio histórico de cuando se añadió el insumo
        $nuevoSubtotal = $cargo->precio_unitario * $nuevaCantidad;

        # Actualizamos el cargo
        $cargo->update([
            'cantidad' => $nuevaCantidad,
            'subtotal' => $nuevoSubtotal,
        ]);

        # Devolvemos el cargo
        return response()->json($cargo->load('insumo'));
    }

    public function eliminar(CitaCargo $cargo)
    {
        # Buscamos el insumo
        $insumo = Insumo::findOrFail($cargo->insumo_id);
        # Devolvemos el stock
        $insumo->increment('stock_actual', $cargo->cantidad);

        # Eliminamos el cargo
        $cargo->delete();

        return response()->json(['mensaje' => 'Cargo eliminado correctamente de la cuenta']);
    }
}

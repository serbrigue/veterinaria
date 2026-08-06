<?php

namespace App\Http\Controllers;

use App\Models\Insumo;
use App\Models\MovimientoInventario;
use App\Http\Requests\GuardarMovimientoInventarioRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MovimientoInventarioController extends Controller
{
    public function registrarMerma(GuardarMovimientoInventarioRequest $request)
    {
        //Validamos la solicitud
        $request->validated();

        //Iniciamos una transacción para asegurar la integridad de los datos
        return DB::transaction(function () use ($request) {
            //Obtenemos el insumo
            $insumo = Insumo::findOrFail($request->insumo_id);

            //Verificamos que la cantidad a mermar no sea mayor al stock actual
            if ($insumo->stock_actual < $request->cantidad) {
                return response()->json(['error' => 'La cantidad a mermar no puede ser mayor al stock actual.'], 422);
            }

            //Decrementamos el stock actual
            $insumo->decrement('stock_actual', $request->cantidad);

            //Registramos el movimiento de inventario
            $movimiento = MovimientoInventario::create([
                'insumo_id' => $insumo->id,
                'tipo' => 'merma',
                'cantidad' => $request->cantidad,
                'motivo' => $request->motivo,
                'usuario_id' => auth()->id(),
            ]);

            //Retornamos el movimiento de inventario
            return response()->json($movimiento->load('insumo'), 201);
        });
    }

    public function registrarCompra(GuardarMovimientoInventarioRequest $request)
    {
        //Validamos la solicitud
        $request->validated();

        //Iniciamos una transacción para asegurar la integridad de los datos
        return DB::transaction(function () use ($request) {
            //Obtenemos el insumo
            $insumo = Insumo::findOrFail($request->insumo_id);

            //Incrementamos el stock actual
            $insumo->increment('stock_actual', $request->cantidad);

            //Registramos el movimiento de inventario
            $movimiento = MovimientoInventario::create([
                'insumo_id' => $insumo->id,
                'tipo' => 'entrada',
                'cantidad' => $request->cantidad,
                'motivo' => $request->motivo,
                'usuario_id' => auth()->id(),
            ]);

            //Retornamos el movimiento de inventario
            return response()->json($movimiento->load('insumo'), 201);
        });
    }

    public function historial(Insumo $insumo)
    {
        //Obtenemos el historial de movimientos del insumo
        $movimientos = MovimientoInventario::with(['usuario', 'cita'])
            ->where('insumo_id', $insumo->id)
            ->orderBy('created_at', 'desc')
            ->get();

        //Retornamos el historial de movimientos
        return response()->json($movimientos);
    }
}

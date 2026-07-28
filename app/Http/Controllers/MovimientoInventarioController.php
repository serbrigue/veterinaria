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
        $request->validated();

        return DB::transaction(function () use ($request) {
            $insumo = Insumo::findOrFail($request->insumo_id);
            
            if ($insumo->stock_actual < $request->cantidad) {
                return response()->json(['error' => 'La cantidad a mermar no puede ser mayor al stock actual.'], 422);
            }

            $insumo->decrement('stock_actual', $request->cantidad);

            $movimiento = MovimientoInventario::create([
                'insumo_id' => $insumo->id,
                'tipo' => 'merma',
                'cantidad' => $request->cantidad,
                'motivo' => $request->motivo,
                'usuario_id' => auth()->id(),
            ]);

            return response()->json($movimiento->load('insumo'), 201);
        });
    }

    public function registrarCompra(GuardarMovimientoInventarioRequest $request)
    {
        $request->validated();

        return DB::transaction(function () use ($request) {
            $insumo = Insumo::findOrFail($request->insumo_id);
            
            $insumo->increment('stock_actual', $request->cantidad);

            $movimiento = MovimientoInventario::create([
                'insumo_id' => $insumo->id,
                'tipo' => 'entrada',
                'cantidad' => $request->cantidad,
                'motivo' => $request->motivo,
                'usuario_id' => auth()->id(),
            ]);

            return response()->json($movimiento->load('insumo'), 201);
        });
    }

    public function historial(Insumo $insumo)
    {
        $movimientos = MovimientoInventario::with(['usuario', 'cita'])
            ->where('insumo_id', $insumo->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($movimientos);
    }
}

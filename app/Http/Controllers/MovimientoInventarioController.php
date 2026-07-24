<?php

namespace App\Http\Controllers;

use App\Models\Insumo;
use App\Models\MovimientoInventario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MovimientoInventarioController extends Controller
{
    public function registrarMerma(Request $request)
    {
        $request->validate([
            'insumo_id' => 'required|exists:insumos,id',
            'cantidad' => 'required|numeric|min:1',
            'motivo' => 'required|string|max:255',
        ]);

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
}

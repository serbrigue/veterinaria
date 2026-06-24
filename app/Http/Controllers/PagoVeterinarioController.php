<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Veterinario;
use Illuminate\Support\Carbon;
use Inertia\Inertia;

class PagoVeterinarioController extends Controller
{
    public function index(Request $request)
    {
        $mes = $request->mes ?? Carbon::now()->month;
        $anio = $request->anio ?? Carbon::now()->year;

        $veterinarios = Veterinario::with(['usuario', 'citas' => function ($q) use ($mes, $anio) {
            $q->where('estado', 'completada')
              ->whereHas('transaccion', function ($t) use ($mes, $anio) {
                  $t->where('estado', 'pagado')
                    ->whereMonth('fecha_pago', $mes)
                    ->whereYear('fecha_pago', $anio);
              })
              ->with('prestacion');
        }])->get();

        $liquidaciones = $veterinarios->map(function ($vet) {
            $totalGenerado = 0;
            $totalComision = 0;
            $serviciosRealizados = 0;

            foreach ($vet->citas as $cita) {
                if ($cita->prestacion) {
                    $serviciosRealizados++;
                    $precio = $cita->prestacion->precio_base;
                    $porcentaje = $cita->prestacion->comision_vet ?? 0;
                    
                    $totalGenerado += $precio;
                    $totalComision += ($precio * $porcentaje) / 100;
                }
            }

            return [
                'id' => $vet->id,
                'nombre' => $vet->usuario ? $vet->usuario->name : 'Desconocido',
                'servicios_realizados' => $serviciosRealizados,
                'total_generado' => $totalGenerado,
                'total_comision' => $totalComision,
            ];
        });

        if ($request->wantsJson()) {
            return response()->json($liquidaciones);
        }

        return Inertia::render('Veterinario/Pagos', [
            'liquidaciones_iniciales' => $liquidaciones,
            'mes_inicial' => $mes,
            'anio_inicial' => $anio
        ]);
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Mascota;
use App\Models\Cita;
use App\Models\Cliente;
use App\Models\Transaccion;
use App\Models\Insumo;
use App\Models\CitaCargo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

class PanelController extends Controller
{
    public function index()
    {
        # Obtenemos el usuario logueado
        $user = Auth::user();
        # Obtenemos los ingresos totales
        $ingresosTotales = Transaccion::where('estado', 'pagado')->sum('monto_total');
        # Obtenemos los ingresos del mes actual
        $ingresosMes = Transaccion::where('estado', 'pagado')
            ->whereMonth('created_at', Carbon::now()->month)
            ->whereYear('created_at', Carbon::now()->year)
            ->sum('monto_total');

        #METRICAS OPERATIVAS
        $citasTotales = Cita::count();
        $citasCompletadas = Cita::where('estado', 'completada')->count();
        $citasCanceladas = Cita::where('estado', 'cancelada')->count();
        $citasAgendadas = Cita::where('estado', 'pendiente')->count();
        $clientesActivos = Cliente::count();
        $cantidadMascotas = Mascota::count();

        #INVENTARIO
        $insumosBajoStock = Insumo::whereColumn('stock_actual', '<=', 'stock_minimo')->count();
        $valorInventario = Insumo::select(DB::raw('SUM(stock_actual * precio_venta) as total'))->value('total') ?? 0;

        #TOP PRESTACIONES
        $topPrestaciones = Cita::whereNotNull('prestacion_id')
            ->select('prestacion_id', DB::raw('count(*) as total'))
            ->groupBy('prestacion_id')
            ->orderByDesc('total')
            ->limit(5)
            ->with('prestacion')
            ->get()
            ->map(fn($cita) => [
                'nombre' => $cita->prestacion->nombre ?? 'Desconocida',
                'cantidad' => $cita->total
            ]);

        #PROXIMAS CITAS
        $proximasCitas = Cita::with(['mascota', 'veterinario.usuario'])
            ->where('estado', 'pendiente')
            ->orderBy('fecha_hora', 'asc')
            ->limit(5)
            ->get();

        #ARRAY CON TODAS LAS ESTADISTICAS
        $estadisticas = [
            'financiero' => [
                'total' => $ingresosTotales,
                'mes' => $ingresosMes,
            ],
            'operativo' => [
                'citas_totales' => $citasTotales,
                'citas_completadas' => $citasCompletadas,
                'citas_canceladas' => $citasCanceladas,
                'citas_agendadas' => $citasAgendadas,
                'clientes' => $clientesActivos,
                'mascotas' => $cantidadMascotas,
            ],
            'inventario' => [
                'bajo_stock' => $insumosBajoStock,
                'valor_total' => $valorInventario,
            ],
            'top_prestaciones' => $topPrestaciones,
            'proximas_citas' => $proximasCitas,
        ];

        #DEVOLVEMOS LA VISTA CON LAS ESTADISTICAS
        return Inertia::render('App/Panel', [
            'estadisticas' => $estadisticas,
        ]);
    }
}

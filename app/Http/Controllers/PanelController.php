<?php

namespace App\Http\Controllers;

use App\Models\Cita;
use App\Models\Cliente;
use App\Models\Insumo;
use App\Models\Mascota;
use App\Models\Transaccion;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

use App\Models\Sucursal;
use App\Models\CitaCargo;
use App\Models\Veterinario;

class PanelController extends Controller
{
    public function index()
    {
        $proximasCitas = Cita::with(['mascota', 'veterinario.usuario'])
            ->where('estado', 'pendiente')
            ->orderBy('fecha_hora', 'asc')
            ->limit(5)
            ->get();

        $estadisticas = [
            'financiero' => $this->getFinancieroStats(),
            'operativo' => $this->getOperativoStats(),
            'inventario' => $this->getInventarioStats(),
            'top_prestaciones' => $this->getTopPrestaciones(),
            'top_insumos' => $this->getTopInsumos(),
            'ingresos_sucursales' => $this->getIngresosSucursales(),
            'veterinarios_estadisticas' => $this->getVeterinariosEstadisticas(),
            'proximas_citas' => $proximasCitas,
        ];

        return Inertia::render('App/Panel', [
            'estadisticas' => $estadisticas,
        ]);
    }

    private function getFinancieroStats()
    {
        return [
            'total' => Transaccion::where('estado', 'pagado')->sum('monto_total'),
            'mes' => Transaccion::where('estado', 'pagado')
                ->whereMonth('created_at', Carbon::now()->month)
                ->whereYear('created_at', Carbon::now()->year)
                ->sum('monto_total'),
        ];
    }

    private function getOperativoStats()
    {
        return [
            'citas_totales' => Cita::count(),
            'citas_completadas' => Cita::where('estado', 'completada')->count(),
            'citas_canceladas' => Cita::where('estado', 'cancelada')->count(),
            'citas_agendadas' => Cita::where('estado', 'pendiente')->count(),
            'clientes' => Cliente::count(),
            'mascotas' => Mascota::count(),
        ];
    }

    private function getInventarioStats()
    {
        return [
            'bajo_stock' => Insumo::whereColumn('stock_actual', '<=', 'stock_minimo')->count(),
            'valor_total' => Insumo::select(DB::raw('SUM(stock_actual * precio_venta) as total'))->value('total') ?? 0,
        ];
    }

    private function getTopPrestaciones()
    {
        return Cita::whereNotNull('prestacion_id')
            ->select('prestacion_id', DB::raw('count(*) as total'))
            ->groupBy('prestacion_id')
            ->orderByDesc('total')
            ->limit(5)
            ->with('prestacion')
            ->get()
            ->map(fn ($cita) => [
                'nombre' => $cita->prestacion->nombre ?? 'Desconocida',
                'cantidad' => $cita->total,
            ]);
    }

    private function getTopInsumos()
    {
        return CitaCargo::whereNotNull('insumo_id')
            ->select('insumo_id', DB::raw('SUM(cantidad) as total_cantidad'))
            ->groupBy('insumo_id')
            ->orderByDesc('total_cantidad')
            ->limit(5)
            ->with('insumo')
            ->get()
            ->map(fn ($cargo) => [
                'nombre' => $cargo->insumo->nombre ?? 'Insumo ' . $cargo->insumo_id,
                'cantidad' => (int) $cargo->total_cantidad,
            ]);
    }

    private function getIngresosSucursales()
    {
        $nombresMeses = [
            1 => 'Ene', 2 => 'Feb', 3 => 'Mar', 4 => 'Abr', 5 => 'May', 6 => 'Jun',
            7 => 'Jul', 8 => 'Ago', 9 => 'Sep', 10 => 'Oct', 11 => 'Nov', 12 => 'Dic'
        ];

        $ultimosMeses = [];
        for ($i = 5; $i >= 0; $i--) {
            $d = Carbon::now()->subMonths($i);
            $ultimosMeses[] = [
                'label' => $nombresMeses[$d->month] . ' ' . $d->year,
                'mes' => $d->month,
                'anio' => $d->year,
            ];
        }

        $sucursales = Sucursal::all();
        $datosSucursales = [];

        foreach ($sucursales as $sucursal) {
            $data = [];
            foreach ($ultimosMeses as $mesInfo) {
                $total = Transaccion::where('transacciones.estado', 'pagado')
                    ->join('citas', 'transacciones.cita_id', '=', 'citas.id')
                    ->join('boxes', 'citas.box_id', '=', 'boxes.id')
                    ->where('boxes.sucursal_id', $sucursal->id)
                    ->whereMonth('transacciones.created_at', $mesInfo['mes'])
                    ->whereYear('transacciones.created_at', $mesInfo['anio'])
                    ->sum('transacciones.monto_total');

                $data[] = (float) $total;
            }
            $datosSucursales[] = [
                'sucursal' => $sucursal->nombre,
                'data' => $data,
            ];
        }

        return [
            'meses' => array_column($ultimosMeses, 'label'),
            'datos_sucursales' => $datosSucursales,
        ];
    }

    private function getVeterinariosEstadisticas()
    {
        return Veterinario::with(['usuario'])
            ->get()
            ->map(function ($vet) {
                $citasCompletadas = Cita::where('veterinario_id', $vet->id)
                    ->where('estado', 'completada')
                    ->count();

                $citasConPago = Cita::where('veterinario_id', $vet->id)
                    ->where('estado', 'completada')
                    ->whereHas('transaccion', function ($t) {
                        $t->where('estado', 'pagado');
                    })
                    ->with('prestacion')
                    ->get();

                $comisiones = $citasConPago->sum(function ($cita) {
                    $precio = $cita->prestacion?->precio_base ?? 0;
                    $porcentaje = $cita->prestacion?->comision_vet ?? 0;
                    return ($precio * $porcentaje) / 100;
                });

                return [
                    'nombre' => $vet->usuario?->name ?? 'Dr. ' . $vet->id,
                    'citas_completadas' => $citasCompletadas,
                    'comisiones_acumuladas' => $comisiones,
                ];
            });
    }
}

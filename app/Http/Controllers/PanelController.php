<?php

namespace App\Http\Controllers;

use App\Models\Cita;
use App\Models\Cliente;
use App\Models\Insumo;
use App\Models\Mascota;
use App\Models\Transaccion;
use App\Models\Box;
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
        //Obtenemos las próximas citas
        $proximasCitas = Cita::with(['mascota', 'veterinario.usuario'])
            ->where('estado', 'pendiente')
            ->orderBy('fecha_hora', 'asc')
            ->limit(5)
            ->get();

        //Obtenemos las estadísticas
        $estadisticas = [
            'financiero' => $this->getFinancieroStats(),
            'operativo' => $this->getOperativoStats(),
            'inventario' => $this->getInventarioStats(),
            'top_prestaciones' => $this->getTopPrestaciones(),
            'top_insumos' => $this->getTopInsumos(),
            'ingresos_sucursales' => $this->getIngresosSucursales(),
            'veterinarios_estadisticas' => $this->getVeterinariosEstadisticas(),
            'proximas_citas' => $proximasCitas,
            'bi_kpis' => $this->getBiKpis(),
        ];

        return Inertia::render('App/Panel', [
            'estadisticas' => $estadisticas,
        ]);
    }

    private function getBiKpis()
    {
        //KPIs Operación Clínica y Eficiencia
        $totalCitas = Cita::count();
        $totalBoxes = Box::count();
        $citasCanceladas = Cita::whereIn('estado', ['cancelada', 'no_asistio'])->count();

        //Obtenemos la tasa de ocupación
        $citasMesActual = Cita::whereMonth('fecha_hora', Carbon::now()->month)->count();
        $horasOcupadas = $citasMesActual * 0.5;
        $horasDisponibles = $totalBoxes * 20 * 8;
        $tasaOcupacion = $horasDisponibles > 0 ? min(100, round(($horasOcupadas / $horasDisponibles) * 100, 2)) : 0;

        //Obtenemos el ticket promedio
        $citasFacturadas = Transaccion::where('estado', 'pagado')->count();
        $ingresosPagados = Transaccion::where('estado', 'pagado')->sum('monto_total');
        $ticketPromedio = $citasFacturadas > 0 ? round($ingresosPagados / $citasFacturadas, 2) : 0;

        //Obtenemos la tasa de ausentismo
        $tasaAusentismo = $totalCitas > 0 ? round(($citasCanceladas / $totalCitas) * 100, 2) : 0;

        //Obtenemos la productividad de los veterinarios
        $productividadVeterinarios = Veterinario::with('usuario')->get()->map(function ($vet) {
            $ingresos = Transaccion::whereHas('cita', function ($q) use ($vet) {
                $q->where('veterinario_id', $vet->id);
            })->where('estado', 'pagado')->sum('monto_total');
            $citas = Cita::where('veterinario_id', $vet->id)->count();
            return [
                'nombre' => $vet->usuario?->name ?? 'Dr. ' . $vet->id,
                'citas_atendidas' => $citas,
                'ingresos_generados' => $ingresos,
            ];
        });

        //Obtenemos los ingresos totales brutos
        $ingresosTotalesBrutos = $ingresosPagados;
        //Obtenemos el costo de nomina variable
        $costoNominaVariable = CitaCargo::sum('pago_vet');

        //Obtenemos las sucursales
        $sucursales = Sucursal::all();

        //Obtenemos el margen neto por sucursal
        $margenNetoSucursales = $sucursales->map(function ($sucursal) {
            //Obtenemos los ingresos por sucursal
            $ingresos = Transaccion::where('transacciones.estado', 'pagado')
                ->join('citas', 'transacciones.cita_id', '=', 'citas.id')
                ->join('boxes', 'citas.box_id', '=', 'boxes.id')
                ->where('boxes.sucursal_id', $sucursal->id)
                ->sum('transacciones.monto_total');

            //Obtenemos los costos de nomina por sucursal
            $costoNomina = CitaCargo::join('citas', 'citas_cargo.cita_id', '=', 'citas.id')
                ->join('boxes', 'citas.box_id', '=', 'boxes.id')
                ->where('boxes.sucursal_id', $sucursal->id)
                ->sum('citas_cargo.pago_vet');

            //Obtenemos el margen neto y el margen porcentual
            $margen = $ingresos - $costoNomina; // Simplified margen
            $margenPorcentaje = $ingresos > 0 ? round(($margen / $ingresos) * 100, 2) : 0;

            //Obtenemos los datos para la tabla
            return [
                'nombre' => $sucursal->nombre,
                'ingresos' => $ingresos,
                'costos' => $costoNomina,
                'margen_neto' => $margen,
                'margen_porcentaje' => $margenPorcentaje
            ];
        });

        //Obtenemos la rotación de insumos
        $rotacionInsumos = CitaCargo::whereNotNull('insumo_id')
            ->select('insumo_id', DB::raw('SUM(cantidad) as total_usado'))
            ->groupBy('insumo_id')
            ->with('insumo')
            ->get()->map(function ($cargo) {
                $stockActual = $cargo->insumo?->stock_actual ?? 1;
                $indice = $stockActual > 0 ? round($cargo->total_usado / $stockActual, 2) : $cargo->total_usado;
                return [
                    'insumo' => $cargo->insumo?->nombre ?? 'Insumo ' . $cargo->insumo_id,
                    'indice_rotacion' => $indice,
                    'total_usado' => $cargo->total_usado
                ];
            });

        //Obtenemos las alertas de stock
        $alertasStock = Insumo::whereColumn('stock_actual', '<=', 'stock_minimo')->get()->map(function ($i) {
            return [
                'id' => $i->id,
                'nombre' => $i->nombre,
                'stock_actual' => $i->stock_actual,
                'stock_minimo' => $i->stock_minimo,
            ];
        });

        //Obtenemos la merma de inventario
        $mermaInventario = [];

        //Obtenemos el total de clientes
        $totalClientes = Cliente::count();
        //Obtenemos el LTV (life time value)
        $ltv = $totalClientes > 0 ? round($ingresosTotalesBrutos / $totalClientes, 2) : 0;

        //Obtenemos el total de mascotas
        $totalMascotas = Mascota::count();
        //Obtenemos la frecuencia de visita
        $citasUltimoAno = Cita::where('fecha_hora', '>=', Carbon::now()->subYear())->count();
        $frecuenciaVisita = $totalMascotas > 0 ? round($citasUltimoAno / $totalMascotas, 2) : 0;

        //Obtenemos la tasa de conversión
        $clientesConCita = Cliente::whereHas('mascotas.citas')->count();
        $tasaConversion = $totalClientes > 0 ? round(($clientesConCita / $totalClientes) * 100, 2) : 0;

        //Retornamos las métricas del panel
        return [
            'operacion' => [
                'tasa_ocupacion_boxes' => $tasaOcupacion,
                'ticket_promedio' => $ticketPromedio,
                'tasa_ausentismo' => $tasaAusentismo,
                'productividad_veterinarios' => $productividadVeterinarios,
            ],
            'financiero' => [
                'ingresos_brutos' => $ingresosTotalesBrutos,
                'costo_nomina_variable' => $costoNominaVariable,
                'margen_neto_sucursal' => $margenNetoSucursales,
            ],
            'inventario' => [
                'rotacion_insumos' => $rotacionInsumos,
                'alertas_stock' => $alertasStock,
                'merma_inventario' => $mermaInventario,
            ],
            'clientes' => [
                'ltv' => $ltv,
                'frecuencia_visita' => $frecuenciaVisita,
                'tasa_conversion' => $tasaConversion,
            ]
        ];
    }

    //Obtenemos las estadísticas financieras
    private function getFinancieroStats()
    {
        //Obtenemos el total de transacciones pagadas
        return [
            'total' => Transaccion::where('estado', 'pagado')->sum('monto_total'),
            //Obtenemos el total de transacciones pagadas del mes actual
            'mes' => Transaccion::where('estado', 'pagado')
                ->whereMonth('created_at', Carbon::now()->month)
                ->whereYear('created_at', Carbon::now()->year)
                ->sum('monto_total'),
        ];
    }

    //Obtenemos las estadísticas operativas
    private function getOperativoStats()
    {
        //Obtenemos las estadísticas operativas
        return [
            'citas_totales' => Cita::count(),
            'citas_completadas' => Cita::where('estado', 'completada')->count(),
            'citas_canceladas' => Cita::where('estado', 'cancelada')->count(),
            'citas_agendadas' => Cita::where('estado', 'pendiente')->count(),
            'clientes' => Cliente::count(),
            'mascotas' => Mascota::count(),
        ];
    }

    //Obtenemos las estadísticas de inventario
    private function getInventarioStats()
    {
        //Obtenemos las estadísticas de inventario
        return [
            'bajo_stock' => Insumo::whereColumn('stock_actual', '<=', 'stock_minimo')->count(),
            'valor_total' => Insumo::select(DB::raw('SUM(stock_actual * precio_venta) as total'))->value('total') ?? 0,
        ];
    }

    private function getTopPrestaciones()
    {
        //Obtenemos las top prestaciones
        return Cita::whereNotNull('prestacion_id')
            ->select('prestacion_id', DB::raw('count(*) as total'))
            ->groupBy('prestacion_id')
            ->orderByDesc('total')
            ->limit(5)
            ->with('prestacion')
            ->get()
            ->map(fn($cita) => [
                'nombre' => $cita->prestacion->nombre ?? 'Desconocida',
                'cantidad' => $cita->total,
            ]);
    }

    private function getTopInsumos()
    {
        //Obtenemos los top insumos
        return CitaCargo::whereNotNull('insumo_id')
            ->select('insumo_id', DB::raw('SUM(cantidad) as total_cantidad'))
            ->groupBy('insumo_id')
            ->orderByDesc('total_cantidad')
            ->limit(5)
            ->with('insumo')
            ->get()
            ->map(fn($cargo) => [
                'nombre' => $cargo->insumo->nombre ?? 'Insumo ' . $cargo->insumo_id,
                'cantidad' => (int) $cargo->total_cantidad,
            ]);
    }

    //Obtenemos los ingresos por sucursal
    private function getIngresosSucursales()
    {
        //Obtenemos los nombres de los meses
        $nombresMeses = [
            1 => 'Ene',
            2 => 'Feb',
            3 => 'Mar',
            4 => 'Abr',
            5 => 'May',
            6 => 'Jun',
            7 => 'Jul',
            8 => 'Ago',
            9 => 'Sep',
            10 => 'Oct',
            11 => 'Nov',
            12 => 'Dic'
        ];

        //Obtenemos los últimos 6 meses
        $ultimosMeses = [];
        for ($i = 5; $i >= 0; $i--) {
            $d = Carbon::now()->subMonths($i);
            $ultimosMeses[] = [
                'label' => $nombresMeses[$d->month] . ' ' . $d->year,
                'mes' => $d->month,
                'anio' => $d->year,
            ];
        }

        //Obtenemos las sucursales
        $sucursales = Sucursal::all();

        //Inicializamos las sucursales
        $datosSucursales = [];

        //Recorremos las sucursales
        foreach ($sucursales as $sucursal) {
            $data = [];
            //Recorremos los últimos 6 meses
            foreach ($ultimosMeses as $mesInfo) {
                //Obtenemos el total de transacciones pagadas
                $total = Transaccion::where('transacciones.estado', 'pagado')
                    ->join('citas', 'transacciones.cita_id', '=', 'citas.id')
                    ->join('boxes', 'citas.box_id', '=', 'boxes.id')
                    ->where('boxes.sucursal_id', $sucursal->id)
                    ->whereMonth('transacciones.created_at', $mesInfo['mes'])
                    ->whereYear('transacciones.created_at', $mesInfo['anio'])
                    ->sum('transacciones.monto_total');

                $data[] = (float) $total;
            }

            //Agregamos las sucursales
            $datosSucursales[] = [
                'sucursal' => $sucursal->nombre,
                'data' => $data,
            ];
        }

        //Retornamos las sucursales
        return [
            'meses' => array_column($ultimosMeses, 'label'),
            'datos_sucursales' => $datosSucursales,
        ];
    }

    //Obtenemos las estadísticas de los veterinarios
    private function getVeterinariosEstadisticas()
    {
        //Obtenemos las estadísticas de los veterinarios
        return Veterinario::with(['usuario'])
            ->get()
            ->map(function ($vet) {
                //Obtenemos las citas completadas de los veterinarios
                $citasCompletadas = Cita::where('veterinario_id', $vet->id)
                    ->where('estado', 'completada')
                    ->count();

                //Obtenemos las citas completadas de los veterinarios con pago
                $citasConPago = Cita::where('veterinario_id', $vet->id)
                    ->where('estado', 'completada')
                    ->whereHas('transaccion', function ($t) {
                        $t->where('estado', 'pagado');
                    })
                    ->with('prestacion')
                    ->get();

                //Calculamos las comisiones de los veterinarios
                $comisiones = $citasConPago->sum(function ($cita) {
                    $precio = $cita->prestacion?->precio_base ?? 0;
                    $porcentaje = $cita->prestacion?->comision_vet ?? 0;
                    return ($precio * $porcentaje) / 100;
                });

                //Retornamos las estadísticas de los veterinarios
                return [
                    'nombre' => $vet->usuario?->name ?? 'Dr. ' . $vet->id,
                    'citas_completadas' => $citasCompletadas,
                    'comisiones_acumuladas' => $comisiones,
                ];
            });
    }
}

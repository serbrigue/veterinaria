<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Veterinario;
use App\Models\PagoVeterinario;
use App\Models\Cita;
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
        }])->paginate(15);

        // Transformar la colección subyacente para inyectar cálculos de comisiones
        $veterinarios->getCollection()->transform(function ($vet) use ($mes, $anio) {
            $totalComision = 0;

            foreach ($vet->citas as $cita) {
                if ($cita->prestacion) {
                    $precio = $cita->prestacion->precio_base;
                    $porcentaje = $cita->prestacion->comision_vet ?? 0;
                    $totalComision += ($precio * $porcentaje) / 100;
                }
            }

            $pagoRealizado = PagoVeterinario::where('veterinario_id', $vet->id)
                ->where('mes', $mes)
                ->where('anio', $anio)
                ->first();

            return [
                'id' => $vet->id,
                'nombre' => $vet->usuario ? $vet->usuario->name : 'Desconocido',
                'total_comision' => $totalComision,
                'estado' => $pagoRealizado ? 'Pagado' : 'Pendiente',
            ];
        });

        // Calcular total general de todas las comisiones del periodo (sin importar paginación)
        $citasMes = Cita::where('estado', 'completada')
            ->whereHas('transaccion', function ($t) use ($mes, $anio) {
                $t->where('estado', 'pagado')
                  ->whereMonth('fecha_pago', $mes)
                  ->whereYear('fecha_pago', $anio);
            })
            ->with('prestacion')->get();

        $totalGeneral = $citasMes->sum(function($cita) {
            $precio = $cita->prestacion->precio_base ?? 0;
            $porcentaje = $cita->prestacion->comision_vet ?? 0;
            return ($precio * $porcentaje) / 100;
        });

        if ($request->wantsJson()) {
            return response()->json([
                'liquidaciones' => $veterinarios,
                'totalGeneral' => $totalGeneral
            ]);
        }

        return Inertia::render('Veterinario/Pagos', [
            'liquidaciones_iniciales' => $veterinarios,
            'total_general_inicial' => $totalGeneral,
            'mes_inicial' => $mes,
            'anio_inicial' => $anio
        ]);
    }

    public function detalle(Request $request, Veterinario $veterinario)
    {
        $mes = $request->mes ?? Carbon::now()->month;
        $anio = $request->anio ?? Carbon::now()->year;

        $veterinario->load('usuario');

        $citas = Cita::with(['mascota.cliente.usuario', 'prestacion', 'transaccion'])
            ->where('veterinario_id', $veterinario->id)
            ->where('estado', 'completada')
            ->whereHas('transaccion', function ($t) use ($mes, $anio) {
                $t->where('estado', 'pagado')
                  ->whereMonth('fecha_pago', $mes)
                  ->whereYear('fecha_pago', $anio);
            })
            ->orderBy('fecha_hora', 'asc')
            ->get();

        $desglose = $citas->map(function ($cita) {
            $precio = $cita->prestacion ? $cita->prestacion->precio_base : 0;
            $porcentaje = $cita->prestacion ? ($cita->prestacion->comision_vet ?? 0) : 0;
            $ganancia = ($precio * $porcentaje) / 100;

            return [
                'id' => $cita->id,
                'fecha' => $cita->transaccion->fecha_pago ?? $cita->fecha_hora,
                'mascota' => $cita->mascota ? $cita->mascota->nombre : 'N/A',
                'cliente' => $cita->mascota && $cita->mascota->cliente && $cita->mascota->cliente->usuario ? $cita->mascota->cliente->usuario->name : 'N/A',
                'servicio' => $cita->prestacion ? $cita->prestacion->nombre : 'Servicio Desconocido',
                'ingreso_clinica' => $precio,
                'comision_porcentaje' => $porcentaje,
                'ganancia_vet' => $ganancia,
            ];
        });

        $totalPagar = $desglose->sum('ganancia_vet');

        $pagoRealizado = PagoVeterinario::where('veterinario_id', $veterinario->id)
            ->where('mes', $mes)
            ->where('anio', $anio)
            ->first();

        if ($request->wantsJson()) {
            return response()->json([
                'desglose' => $desglose,
                'total' => $totalPagar,
                'estado' => $pagoRealizado ? 'Pagado' : 'Pendiente'
            ]);
        }

        return Inertia::render('Veterinario/PagoDetalle', [
            'veterinario' => [
                'id' => $veterinario->id,
                'nombre' => $veterinario->usuario->name,
            ],
            'desglose_inicial' => $desglose,
            'total_inicial' => $totalPagar,
            'estado_inicial' => $pagoRealizado ? 'Pagado' : 'Pendiente',
            'mes_inicial' => $mes,
            'anio_inicial' => $anio
        ]);
    }

    public function procesarPago(Request $request, Veterinario $veterinario)
    {
        $request->validate([
            'mes' => 'required|integer|min:1|max:12',
            'anio' => 'required|integer',
            'monto_total' => 'required|numeric'
        ]);

        $existe = PagoVeterinario::where('veterinario_id', $veterinario->id)
            ->where('mes', $request->mes)
            ->where('anio', $request->anio)
            ->exists();

        if ($existe) {
            return response()->json(['error' => 'Ya se ha registrado un pago para este mes y año.'], 422);
        }

        $pago = PagoVeterinario::create([
            'veterinario_id' => $veterinario->id,
            'mes' => $request->mes,
            'anio' => $request->anio,
            'monto_total' => $request->monto_total,
            'estado' => 'pagado',
        ]);

        return response()->json(['mensaje' => 'Pago registrado exitosamente.', 'pago' => $pago]);
    }
}

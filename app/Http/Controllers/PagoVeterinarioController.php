<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Veterinario;
use App\Models\PagoVeterinario;
use App\Models\Cita;
use App\Models\User;
use App\Models\Rol;
use Illuminate\Support\Carbon;
use Inertia\Inertia;

class PagoVeterinarioController extends Controller
{
    public function index(Request $request)
    {
        $mes = $request->mes ?? Carbon::now()->month;
        $anio = $request->anio ?? Carbon::now()->year;

        // Obtener todos los roles liquidables (excluyendo admin y cliente)
        $roles = Rol::whereNotIn('nombre_interno', ['admin', 'cliente'])->get();

        // Rol por defecto: veterinario
        $rolVet = Rol::where('nombre_interno', 'veterinario')->first();
        $rolId = $request->rol_id ?? ($rolVet ? $rolVet->id : null);

        $liquidaciones = collect();

        if ($rolId) {
            $rolSelected = Rol::find($rolId);

            if ($rolSelected) {
                // Obtener todos los usuarios con ese rol
                $usuarios = User::where('rol_id', $rolId)->with(['rol'])->paginate(15);

                $usuarios->getCollection()->transform(function ($user) use ($mes, $anio, $rolSelected) {
                    $totalComision = 0;

                    if ($rolSelected->nombre_interno === 'veterinario') {
                        $vet = Veterinario::where('user_id', $user->id)->first();
                        if ($vet) {
                            $citas = Cita::where('veterinario_id', $vet->id)
                                ->where('estado', 'completada')
                                ->whereHas('transaccion', function ($t) use ($mes, $anio) {
                                    $t->where('estado', 'pagado')
                                      ->whereMonth('fecha_pago', $mes)
                                      ->whereYear('fecha_pago', $anio);
                                })
                                ->with('prestacion')
                                ->get();

                            foreach ($citas as $cita) {
                                if ($cita->prestacion) {
                                    $precio = $cita->prestacion->precio_base;
                                    $porcentaje = $cita->prestacion->comision_vet ?? 0;
                                    $totalComision += ($precio * $porcentaje) / 100;
                                }
                            }
                        }
                    } else {
                        // Personal de apoyo
                        $citas = Cita::whereHas('equipoMedico', function ($em) use ($user) {
                                $em->where('usuario_id', $user->id);
                            })
                            ->where('estado', 'completada')
                            ->whereHas('transaccion', function ($t) use ($mes, $anio) {
                                $t->where('estado', 'pagado')
                                  ->whereMonth('fecha_pago', $mes)
                                  ->whereYear('fecha_pago', $anio);
                            })
                            ->with('prestacion')
                            ->get();

                        foreach ($citas as $cita) {
                            if ($cita->prestacion) {
                                $precio = $cita->prestacion->precio_base;
                                $porcentaje = $cita->prestacion->comision_equipo ?? 0;
                                $totalComision += ($precio * $porcentaje) / 100;
                            }
                        }
                    }

                    $pagoRealizado = PagoVeterinario::where('usuario_id', $user->id)
                        ->where('mes', $mes)
                        ->where('anio', $anio)
                        ->first();

                    return [
                        'id' => $user->id,
                        'nombre' => $user->name,
                        'rol' => $user->rol ? $user->rol->nombre_legible : 'Personal',
                        'total_comision' => $totalComision,
                        'estado' => $pagoRealizado ? 'Pagado' : 'Pendiente',
                    ];
                });

                $liquidaciones = $usuarios;
            }
        }

        // Calcular total general de todas las comisiones del periodo para el rol seleccionado
        $totalGeneral = 0;
        if ($rolId) {
            $rolSelected = Rol::find($rolId);
            if ($rolSelected) {
                if ($rolSelected->nombre_interno === 'veterinario') {
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
                } else {
                    $citasMes = Cita::whereHas('equipoMedico', function($em) use ($rolId) {
                            $em->where('rol_id', $rolId);
                        })
                        ->where('estado', 'completada')
                        ->whereHas('transaccion', function ($t) use ($mes, $anio) {
                            $t->where('estado', 'pagado')
                              ->whereMonth('fecha_pago', $mes)
                              ->whereYear('fecha_pago', $anio);
                        })
                        ->with('prestacion')->get();

                    $totalGeneral = $citasMes->sum(function($cita) {
                        $precio = $cita->prestacion->precio_base ?? 0;
                        $porcentaje = $cita->prestacion->comision_equipo ?? 0;
                        return ($precio * $porcentaje) / 100;
                    });
                }
            }
        }

        if ($request->wantsJson()) {
            return response()->json([
                'liquidaciones' => $liquidaciones,
                'totalGeneral' => $totalGeneral
            ]);
        }

        return Inertia::render('Veterinario/Pagos', [
            'liquidaciones_iniciales' => $liquidaciones,
            'total_general_inicial' => $totalGeneral,
            'roles' => $roles,
            'rol_id_inicial' => (int)$rolId,
            'mes_inicial' => (int)$mes,
            'anio_inicial' => (int)$anio
        ]);
    }

    public function detalle(Request $request, User $usuario)
    {
        $mes = $request->mes ?? Carbon::now()->month;
        $anio = $request->anio ?? Carbon::now()->year;

        $usuario->load('rol');

        if ($usuario->rol && $usuario->rol->nombre_interno === 'veterinario') {
            $vet = Veterinario::where('user_id', $usuario->id)->first();
            $citas = $vet ? Cita::with(['mascota.cliente.usuario', 'prestacion', 'transaccion'])
                ->where('veterinario_id', $vet->id)
                ->where('estado', 'completada')
                ->whereHas('transaccion', function ($t) use ($mes, $anio) {
                    $t->where('estado', 'pagado')
                      ->whereMonth('fecha_pago', $mes)
                      ->whereYear('fecha_pago', $anio);
                })
                ->orderBy('fecha_hora', 'asc')
                ->get() : collect();

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
                    'ganancia_personal' => $ganancia,
                ];
            });
        } else {
            $citas = Cita::with(['mascota.cliente.usuario', 'prestacion', 'transaccion'])
                ->whereHas('equipoMedico', function ($em) use ($usuario) {
                    $em->where('usuario_id', $usuario->id);
                })
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
                $porcentaje = $cita->prestacion ? ($cita->prestacion->comision_equipo ?? 0) : 0;
                $ganancia = ($precio * $porcentaje) / 100;

                return [
                    'id' => $cita->id,
                    'fecha' => $cita->transaccion->fecha_pago ?? $cita->fecha_hora,
                    'mascota' => $cita->mascota ? $cita->mascota->nombre : 'N/A',
                    'cliente' => $cita->mascota && $cita->mascota->cliente && $cita->mascota->cliente->usuario ? $cita->mascota->cliente->usuario->name : 'N/A',
                    'servicio' => $cita->prestacion ? $cita->prestacion->nombre : 'Servicio Desconocido',
                    'ingreso_clinica' => $precio,
                    'comision_porcentaje' => $porcentaje,
                    'ganancia_personal' => $ganancia,
                ];
            });
        }

        $totalPagar = $desglose->sum('ganancia_personal');

        $pagoRealizado = PagoVeterinario::where('usuario_id', $usuario->id)
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
            'personal' => [
                'id' => $usuario->id,
                'nombre' => $usuario->name,
                'rol' => $usuario->rol ? $usuario->rol->nombre_legible : 'Personal',
            ],
            'desglose_inicial' => $desglose,
            'total_inicial' => $totalPagar,
            'estado_inicial' => $pagoRealizado ? 'Pagado' : 'Pendiente',
            'mes_inicial' => (int)$mes,
            'anio_inicial' => (int)$anio
        ]);
    }

    public function procesarPago(Request $request, User $usuario)
    {
        $request->validate([
            'mes' => 'required|integer|min:1|max:12',
            'anio' => 'required|integer',
            'monto_total' => 'required|numeric'
        ]);

        $existe = PagoVeterinario::where('usuario_id', $usuario->id)
            ->where('mes', $request->mes)
            ->where('anio', $request->anio)
            ->exists();

        if ($existe) {
            return response()->json(['error' => 'Ya se ha registrado un pago para este mes y año.'], 422);
        }

        $vet = Veterinario::where('user_id', $usuario->id)->first();

        $pago = PagoVeterinario::create([
            'usuario_id' => $usuario->id,
            'veterinario_id' => $vet ? $vet->id : null,
            'mes' => $request->mes,
            'anio' => $request->anio,
            'monto_total' => $request->monto_total,
            'estado' => 'pagado',
        ]);

        return response()->json(['mensaje' => 'Pago registrado exitosamente.', 'pago' => $pago]);
    }
}


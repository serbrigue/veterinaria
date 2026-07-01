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
        # Obtenemos el mes y el año de la solicitud
        $mes = $request->mes ?? Carbon::now()->month;
        $anio = $request->anio ?? Carbon::now()->year;

        # Obtenemos todos los roles liquidables (excluyendo admin y cliente)
        $roles = Rol::whereNotIn('nombre_interno', ['admin', 'cliente'])->get();

        # Rol por defecto: veterinario
        $rolVet = Rol::where('nombre_interno', 'veterinario')->first();
        $rolId = $request->rol_id ?? ($rolVet ? $rolVet->id : null);

        # Inicializamos las liquidaciones, usamos collection porque es para mostrar datos en la vista
        $liquidaciones = collect();

        # Si el rol es veterinario
        if ($rolId) {
            # Obtenemos el rol seleccionado
            $rolSelected = Rol::find($rolId);


            if ($rolSelected) {
                # Obtenemos todos los usuarios con ese rol
                $usuarios = User::where('rol_id', $rolId)->with(['rol'])->paginate(15);

                # Recorremos todas las citas para calcular la comisión, usamos transform para modificar la colección
                $usuarios->getCollection()->transform(function ($user) use ($mes, $anio, $rolSelected) {
                    # Inicializamos la comisión
                    $totalComision = 0;

                    # Si el rol es veterinario
                    if ($rolSelected->nombre_interno === 'veterinario') {
                        # Obtenemos el veterinario
                        $vet = Veterinario::where('user_id', $user->id)->first();

                        if ($vet) {
                            # Obtenemos todas las citas completadas del veterinario
                            $citas = Cita::where('veterinario_id', $vet->id)
                                ->where('estado', 'completada')
                                ->whereHas('transaccion', function ($t) use ($mes, $anio) {
                                    $t->where('estado', 'pagado')
                                        ->whereMonth('fecha_pago', $mes)
                                        ->whereYear('fecha_pago', $anio);
                                })
                                ->with('prestacion')
                                ->get();

                            # Obtenemos todas las citas completadas del veterinario
                            foreach ($citas as $cita) {
                                # Verificamos que la prestacion exista
                                if ($cita->prestacion) {
                                    # Obtenemos el precio base de la prestacion
                                    $precio = $cita->prestacion->precio_base;
                                    # Obtenemos el porcentaje de comisión del veterinario
                                    $porcentaje = $cita->prestacion->comision_vet ?? 0;
                                    # Calculamos la comisión
                                    $totalComision += ($precio * $porcentaje) / 100;
                                }
                            }
                        }
                    } else {
                        # Obtenemos todas las citas completadas del personal de apoyo
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

                        # Recorremos todas las citas para calcular la comisión
                        foreach ($citas as $cita) {
                            # Verificamos que la prestacion exista
                            if ($cita->prestacion) {
                                # Obtenemos el precio base de la prestacion
                                $precio = $cita->prestacion->precio_base;
                                # Obtenemos el porcentaje de comisión del personal de apoyo
                                $porcentaje = $cita->prestacion->comision_equipo ?? 0;
                                # Calculamos la comisión
                                $totalComision += ($precio * $porcentaje) / 100;
                            }
                        }
                    }

                    # Obtenemos si el pago ha sido realizado
                    $pagoRealizado = PagoVeterinario::where('usuario_id', $user->id)
                        ->where('mes', $mes)
                        ->where('anio', $anio)
                        ->first();

                    # Devolvemos la liquidación
                    return [
                        'id' => $user->id,
                        'nombre' => $user->name,
                        'rol' => $user->rol ? $user->rol->nombre_legible : 'Personal',
                        'total_comision' => $totalComision,
                        'estado' => $pagoRealizado ? 'Pagado' : 'Pendiente',
                    ];
                });
                # Devolvemos todas las liquidaciones
                $liquidaciones = $usuarios;
            }
        }

        # Calculamos el total general
        $totalGeneral = 0;
        # Si el rol es veterinario
        if ($rolId) {
            # Obtenemos el rol seleccionado
            $rolSelected = Rol::find($rolId);
            # Si el rol es veterinario
            if ($rolSelected) {
                # Si el rol es veterinario
                if ($rolSelected->nombre_interno === 'veterinario') {
                    # Obtenemos todas las citas completadas del veterinario
                    $citasMes = Cita::where('estado', 'completada')
                        ->whereHas('transaccion', function ($t) use ($mes, $anio) {
                            $t->where('estado', 'pagado')
                                ->whereMonth('fecha_pago', $mes)
                                ->whereYear('fecha_pago', $anio);
                        })
                        ->with('prestacion')->get();

                    # Calculamos el total general
                    $totalGeneral = $citasMes->sum(function ($cita) {
                        $precio = $cita->prestacion->precio_base ?? 0;
                        $porcentaje = $cita->prestacion->comision_vet ?? 0;
                        return ($precio * $porcentaje) / 100;
                    });
                } else {
                    # Obtenemos todas las citas completadas del personal de apoyo
                    $citasMes = Cita::whereHas('equipoMedico', function ($em) use ($rolId) {
                        $em->where('rol_id', $rolId);
                    })
                        ->where('estado', 'completada')
                        ->whereHas('transaccion', function ($t) use ($mes, $anio) {
                            $t->where('estado', 'pagado')
                                ->whereMonth('fecha_pago', $mes)
                                ->whereYear('fecha_pago', $anio);
                        })
                        ->with('prestacion')->get();

                    # Calculamos el total general
                    $totalGeneral = $citasMes->sum(function ($cita) {
                        $precio = $cita->prestacion->precio_base ?? 0;
                        $porcentaje = $cita->prestacion->comision_equipo ?? 0;
                        return ($precio * $porcentaje) / 100;
                    });
                }
            }
        }

        # Si la solicitud es en formato JSON
        if ($request->wantsJson()) {
            # Devolvemos las liquidaciones
            return response()->json([
                'liquidaciones' => $liquidaciones,
                'totalGeneral' => $totalGeneral
            ]);
        }

        # Devolvemos la vista
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
        # Obtenemos el mes y el año
        $mes = $request->mes ?? Carbon::now()->month;
        $anio = $request->anio ?? Carbon::now()->year;

        # Obtenemos el rol del usuario
        $usuario->load('rol');

        # Si el rol es veterinario
        if ($usuario->rol && $usuario->rol->nombre_interno === 'veterinario') {
            # Obtenemos el veterinario
            $vet = Veterinario::where('user_id', $usuario->id)->first();
            # Obtenemos las citas completadas del veterinario
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

            # Obtenemos el desglose
            $desglose = $citas->map(function ($cita) {
                # Obtenemos el precio de la prestacion
                $precio = $cita->prestacion ? $cita->prestacion->precio_base : 0;
                # Obtenemos el porcentaje de comisión del veterinario
                $porcentaje = $cita->prestacion ? ($cita->prestacion->comision_vet ?? 0) : 0;
                # Calculamos la comisión
                $ganancia = ($precio * $porcentaje) / 100;

                # Devolvemos el desglose
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
            # Obtenemos todas las citas del personal de apoyo
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

            # Obtenemos el desglose
            $desglose = $citas->map(function ($cita) {
                # Obtenemos el precio de la prestacion
                $precio = $cita->prestacion ? $cita->prestacion->precio_base : 0;
                # Obtenemos el porcentaje de comisión del personal de apoyo
                $porcentaje = $cita->prestacion ? ($cita->prestacion->comision_equipo ?? 0) : 0;
                # Calculamos la comisión
                $ganancia = ($precio * $porcentaje) / 100;

                # Devolvemos el desglose
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

        # Obtenemos el total a pagar
        $totalPagar = $desglose->sum('ganancia_personal');

        # Obtenemos el pago realizado
        $pagoRealizado = PagoVeterinario::where('usuario_id', $usuario->id)
            ->where('mes', $mes)
            ->where('anio', $anio)
            ->first();

        # Devolvemos la respuesta en caso de solicitud JSON
        if ($request->wantsJson()) {
            # Devolvemos el desglose
            return response()->json([
                'desglose' => $desglose,
                # Devolvemos el total a pagar
                'total' => $totalPagar,
                # Devolvemos el estado
                'estado' => $pagoRealizado ? 'Pagado' : 'Pendiente'
            ]);
        }

        # Devolvemos la vista

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

        # Validamos los datos de la solicitud
        $request->validate([
            'mes' => 'required|integer|min:1|max:12',
            'anio' => 'required|integer',
            'monto_total' => 'required|numeric'
        ]);

        # Verificamos si ya existe un pago para el mes y año
        $existe = PagoVeterinario::where('usuario_id', $usuario->id)
            ->where('mes', $request->mes)
            ->where('anio', $request->anio)
            ->exists();

        # Si existe un pago para el mes y año, devolvemos un mensaje de error
        if ($existe) {
            return response()->json(['error' => 'Ya se ha registrado un pago para este mes y año.'], 422);
        }

        # Obtenemos el veterinario
        $vet = Veterinario::where('user_id', $usuario->id)->first();

        # Creamos el pago
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

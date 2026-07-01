<?php

namespace App\Http\Controllers;

use App\Models\Cita;
use App\Models\Mascota;
use App\Models\CitaCargo;
use App\Models\Insumo;
use App\Models\Prestacion;
use App\Models\Transaccion;
use \Illuminate\Support\Carbon;
use App\Models\Sucursal;
use App\Models\Box;
use App\Models\Veterinario;
use App\Models\Rol;
use App\Models\User;
use App\Http\Requests\GuardarCitaRequest;
use App\Http\Requests\ActualizarCitaRequest;
use Inertia\Inertia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class CitaController extends Controller
{

    public function listado(Request $request)
    {
        # Verificamos si el usuario es administrador o veterinario
        if (auth()->user()->isAdmin() || auth()->user()->isVeterinario()) {
            #Si lo es, traemos todas las mascotas
            $mascotas = Mascota::with('cliente.usuario', 'raza.especie')->get();
        } else {
            #Si no, traemos solo las mascotas del cliente
            $mascotas = Mascota::where('cliente_id', auth()->user()->cliente?->id)->get();
        }

        # Filtros de citas, con eager loading

        $query = Cita::with(['mascota.cliente.usuario', 'veterinario.usuario', 'box', 'transaccion'])
            ->when($request->filled('mascota_id'), fn($q) => $q->where('mascota_id', $request->mascota_id))
            ->when($request->filled('veterinario_id'), fn($q) => $q->where('veterinario_id', $request->veterinario_id))
            ->when($request->filled('sucursal_id'), fn($q) => $q->whereHas('box', fn($b) => $b->where('sucursal_id', $request->sucursal_id)))
            ->when($request->filled('titulo'), fn($q) => $q->where('titulo', 'like', '%' . $request->titulo . '%'))
            ->when($request->filled('estado'), fn($q) => $q->where('estado', $request->estado));

        # Si el usuario no es administrador ni veterinario

        if (!auth()->user()->isAdmin() && !auth()->user()->isVeterinario()) {
            # Traemos solo las citas del cliente
            $clienteId = auth()->user()->cliente?->id;
            $query->whereHas('mascota', fn($q) => $q->where('cliente_id', $clienteId));
            # Aparte si no se especifica estado, no mostramos canceladas
            if (!$request->filled('estado')) {
                $query->where('estado', '!=', 'cancelada');
            }
        }

        # Paginamos los resultados
        $citas = $query->orderBy('fecha_hora', 'desc')->paginate(15);

        # Traemos las sucursales con eager loading
        $sucursales = Cache::remember('sucursales_full', now()->addMinutes(30), function () {
            return Sucursal::with(['veterinarios.usuario', 'boxes'])->orderBy('nombre')->get();
        });

        # Traemos las prestaciones con eager loading
        $prestaciones = Cache::remember('prestaciones_full', now()->addMinutes(30), function () {
            return Prestacion::with(['sucursal', 'especialidad'])->orderBy('nombre')->get();
        });

        # Traemos los veterinarios con eager loading
        $veterinarios = Cache::remember('veterinarios_simple', now()->addMinutes(30), function () {
            return Veterinario::all();
        });

        # Si la solicitud es en formato JSON, devolvemos JSON
        if ($request->wantsJson()) {
            return response()->json([
                'citas' => $citas,
                'mascotas' => $mascotas,
                'sucursales' => $sucursales,
                'prestaciones' => $prestaciones,
            ]);
        }

        # Devolvemos la vista con los datos
        return Inertia::render('Cita/Listado', [
            'citas' => $citas,
            'mascotas' => $mascotas,
            'sucursales' => $sucursales,
            'prestaciones' => $prestaciones,
            'veterinarios' => $veterinarios,
        ]);
    }

    public function obtenerTodas()
    {
        # Si es admin o veterinario, traemos todas las citas
        if (auth()->user()->isAdmin() || auth()->user()->isVeterinario()) {
            return Cita::with(['mascota.cliente.usuario', 'veterinario.usuario'])->get();
        }

        # Si no es admin ni veterinario, traemos solo las citas del cliente
        $clienteId = auth()->user()->cliente?->id;

        # Traemos las citas con eager loading, si no hay cliente no traemos nada
        return Cita::whereHas('mascota', function ($query) use ($clienteId) {
            $query->where('cliente_id', $clienteId);
        })->with(['mascota.cliente.usuario', 'veterinario.usuario', 'box'])->get();
    }


    public function crear(GuardarCitaRequest $solicitud)
    {
        # Validamos la solicitud
        $data = $solicitud->validated();

        # Iniciamos una transacción para evitar condiciones de carrera
        return DB::transaction(function () use ($data) {

            # Obtenemos la hora de termino de la cita
            $horaTermino = Carbon::parse($data['fecha_hora'])->addMinutes(30);

            # Obtenemos el box y la prestacion
            $box        = Box::find($data['box_id']);
            $prestacion = Prestacion::find($data['prestacion_id']);

            # Validamos que el box sea compatible con la prestacion
            if ($box && $box->categoria_prestacion_id !== null) {

                # Si la prestacion tiene categoria y es diferente a la del box, entonces no son compatibles
                if ($prestacion && $box->categoria_prestacion_id !== $prestacion->categoria_prestacion_id) {
                    return response()->json([
                        'error' => 'El box "' . $box->nombre . '" no es compatible con el tipo de prestación seleccionada.'
                    ], 422);
                }
            }

            # Bloqueamos los recursos padre para evitar condiciones de carrera (doble reserva concurrente)
            Veterinario::where('id', $data['veterinario_id'])->lockForUpdate()->first();
            Box::where('id', $data['box_id'])->lockForUpdate()->first();

            # Verificamos si hay solapamiento de citas con el veterinario
            $solapamientoCitasVeterinario = Cita::where('veterinario_id', $data['veterinario_id'])
                ->where('fecha_hora', '<', $horaTermino)
                ->where('hora_termino', '>', Carbon::parse($data['fecha_hora']))
                ->where('estado', '!=', 'cancelada')
                ->exists();



            # Verificamos si hay solapamiento de citas con el box
            $solapamientoCitasBox = Cita::where('box_id', $data['box_id'])
                ->where('fecha_hora', '<', $horaTermino)
                ->where('hora_termino', '>', Carbon::parse($data['fecha_hora']))
                ->where('estado', '!=', 'cancelada')
                ->exists();

            #Si existen solapamientos con el veterinario o el box, no se puede agendar la cita
            if ($solapamientoCitasVeterinario) {
                return response()->json(['error' => 'No se puede agendar la cita, el veterinario ya está ocupado en ese horario'], 409);
            }
            if ($solapamientoCitasBox) {
                return response()->json(['error' => 'No se puede agendar la cita, el box ya está ocupado en ese horario'], 409);
            }

            # Creamos la cita
            $cita = Cita::create([
                'titulo'        => $data['titulo'],
                'descripcion'   => $data['descripcion'],
                'fecha_hora'    => $data['fecha_hora'],
                'hora_termino'  => $horaTermino,
                'estado'        => 'pendiente',
                'mascota_id'    => $data['mascota_id'],
                'veterinario_id' => $data['veterinario_id'],
                'box_id'        => $data['box_id'],
                'prestacion_id' => $data['prestacion_id'],
            ]);

            # Retornamos la cita
            return response()->json($cita, 201);
        });
    }


    public function actualizar(ActualizarCitaRequest $solicitud, Cita $cita)

    {

        # Validamos la solicitud
        $data = $solicitud->validated();

        # Iniciamos una transacción para evitar condiciones de carrera
        return DB::transaction(function () use ($data, $cita) {
            # Obtenemos la hora de termino de la cita
            $horaTermino = Carbon::parse($data['fecha_hora'])->addMinutes(30);

            # Obtenemos el box y la prestacion
            $box        = Box::find($data['box_id']);
            $prestacion = Prestacion::find($data['prestacion_id']);

            # Validamos que el box sea compatible con la prestacion
            if ($box && $box->categoria_prestacion_id !== null) {
                if ($prestacion && $box->categoria_prestacion_id !== $prestacion->categoria_prestacion_id) {

                    # Retornamos un error si el box no es compatible con la prestacion
                    return response()->json([
                        'error' => 'El box "' . $box->nombre . '" no es compatible con el tipo de prestación seleccionada.'
                    ], 422);
                }
            }

            # Bloqueamos los recursos padre para evitar condiciones de carrera
            Veterinario::where('id', $data['veterinario_id'])->lockForUpdate()->first();
            Box::where('id', $data['box_id'])->lockForUpdate()->first();

            # Verificamos si hay solapamiento de citas con el veterinario
            $solapamientoCitasVeterinario = Cita::where('veterinario_id', $data['veterinario_id'])
                ->where('id', '!=', $cita->id)
                ->where('fecha_hora', '<', $horaTermino)
                ->where('hora_termino', '>', Carbon::parse($data['fecha_hora']))
                ->where('estado', '!=', 'cancelada')
                ->exists();

            # Verificamos si hay solapamiento de citas con el box
            $solapamientoCitasBox = Cita::where('box_id', $data['box_id'])
                ->where('id', '!=', $cita->id)
                ->where('fecha_hora', '<', $horaTermino)
                ->where('hora_termino', '>', Carbon::parse($data['fecha_hora']))
                ->where('estado', '!=', 'cancelada')
                ->exists();

            # Si existe solapamiento con el veterinario, retornamos un error
            if ($solapamientoCitasVeterinario) {
                return response()->json(['error' => 'No se puede actualizar la cita, el veterinario ya está ocupado en ese horario'], 409);
            }
            # Si existe solapamiento con el box, retornamos un error
            if ($solapamientoCitasBox) {
                return response()->json(['error' => 'No se puede actualizar la cita, el box ya está ocupado en ese horario'], 409);
            }

            # Actualizamos la cita
            $cita->update(array_merge($data, ['hora_termino' => $horaTermino]));

            # Retornamos la cita
            return response()->json($cita);
        });
    }




    public function horariosDisponibles(Request $request)
    {

        # Validamos la solicitud
        $request->validate([
            'fecha'          => 'required|date_format:Y-m-d',
            'veterinario_id' => 'required|exists:veterinarios,id',
            'box_id'         => 'required|exists:boxes,id',
        ]);

        # Obtenemos los datos de la solicitud
        $fecha    = $request->fecha;
        $duracion = 30; // minutos por slot

        # Obtenemos las citas del veterinario
        $citasVet = Cita::where('veterinario_id', $request->veterinario_id)
            ->whereDate('fecha_hora', $fecha)
            ->where('estado', '!=', 'cancelada')
            ->get(['fecha_hora', 'hora_termino']);

        # Obtenemos las citas del box
        $citasBox = Cita::where('box_id', $request->box_id)
            ->whereDate('fecha_hora', $fecha)
            ->where('estado', '!=', 'cancelada')
            ->get(['fecha_hora', 'hora_termino']);

        # Genera todos los slots de 30 min entre $inicio y $fin con el tipo indicado
        $generarSlots = function (Carbon $inicio, Carbon $fin, string $tipo) use ($duracion, $citasVet, $citasBox) {
            $slots  = [];
            $cursor = $inicio->copy();

            # Recorremos los slots de 30 minutos
            while ($cursor->lt($fin)) {
                $slotFin = $cursor->copy()->addMinutes($duracion);

                # Verificamos si el slot está ocupado por el veterinario
                $ocupadoVet = $citasVet->some(
                    fn($c) => Carbon::parse($c->fecha_hora)->lt($slotFin)
                        && Carbon::parse($c->hora_termino)->gt($cursor)
                );

                # Verificamos si el slot está ocupado por el box
                $ocupadoBox = $citasBox->some(
                    fn($c) => Carbon::parse($c->fecha_hora)->lt($slotFin)
                        && Carbon::parse($c->hora_termino)->gt($cursor)
                );

                # Agregamos el slot a la lista
                $slots[] = [
                    'hora'       => $cursor->format('H:i'),
                    'fecha_hora' => $cursor->toDateTimeString(),
                    'disponible' => !$ocupadoVet && !$ocupadoBox,
                    'tipo'       => $tipo,
                ];

                # Avanzamos al siguiente slot
                $cursor->addMinutes($duracion);
            }

            # Retornamos los slots
            return $slots;
        };

        # Retornamos los slots de 'normal' y 'urgencia'
        return response()->json([
            'normal'   => $generarSlots(
                Carbon::parse($fecha)->setTime(9, 0),
                Carbon::parse($fecha)->setTime(18, 0),
                'normal'
            ),
            'urgencia' => $generarSlots(
                Carbon::parse($fecha)->setTime(18, 0),
                Carbon::parse($fecha)->setTime(21, 30),
                'urgencia'
            ),
        ]);
    }

    public function cancelar(Request $request, Cita $cita)
    {

        # Verificamos si la cita ya está cancelada
        if ($cita->estado === 'cancelada') {
            return response()->json(['mensaje' => 'La cita ya estaba cancelada'], 422);
        }

        # Obtenemos el motivo de la cancelación
        $motivo = $request->input('motivo_cancelacion', 'Cancelada sin motivo especificado.');

        # Actualizamos la cita
        $cita->update([
            'estado' => 'cancelada',
            'notas' => $motivo
        ]);

        # Retornamos la cita
        return response()->json(['mensaje' => 'Cita cancelada correctamente']);
    }

    public function detalle(Cita $cita)
    {

        # Cargamos las relaciones de la cita
        $cita->load([
            'mascota.cliente.usuario',
            'veterinario.usuario',
            'box.sucursal',
            'transaccion',
            'prestacion.categoriaPrestacion',
            'equipoMedico.usuario',
            'equipoMedico.rol'
        ]);

        # Obtenemos la mascota
        $mascota = Mascota::with([
            'cliente.usuario',
            'raza.especie',
        ])->find($cita->mascota_id);

        # Obtenemos los cargos registrados para esta cita
        $cargos = CitaCargo::where('cita_id', $cita->id)
            ->with(['prestacion', 'insumo'])
            ->get();

        # Obtenemos los insumos disponibles en la sucursal del veterinario asignado
        $insumosSucursal = [];
        if ($cita->veterinario && $cita->box?->sucursal_id) {
            $insumosSucursal = Insumo::where('sucursal_id', $cita->box->sucursal_id)
                ->where('stock_actual', '>', 0)
                ->orderBy('nombre')
                ->get(['id', 'nombre', 'precio_venta', 'stock_actual']);
        }

        # Obtenemos el personal médico adicional si la cita es una cirugía
        $rolesMedicos = [];
        $usuariosMedicos = [];

        # Si la cita es una cirugía, obtenemos el personal médico adicional
        if ($cita->prestacion?->categoriaPrestacion?->nombre === 'Cirugia') {
            # Obtenemos los roles médicos
            $rolesMedicos = Rol::whereIn('nombre_interno', ['anestesista', 'arsenalero', 'tens', 'enfermero'])->get();
            # Obtenemos los usuarios médicos
            $usuariosMedicos = User::whereIn('rol_id', $rolesMedicos->pluck('id'))->with('rol')->orderBy('name')->get();
        }

        # Retornamos la vista de detalle
        return Inertia::render('Cita/Detalle', [
            'cita'            => $cita,
            'cargos'          => $cargos,
            'insumosSucursal' => $insumosSucursal,
            'mascota'         => $mascota,
            'prestacion'      => $cita->prestacion,
            'rolesMedicos'    => $rolesMedicos,
            'usuariosMedicos' => $usuariosMedicos,
        ]);
    }

    public function actualizarNotas(Request $request, Cita $cita)
    {

        # Validamos la solicitud
        $request->validate(['notas' => 'nullable|string']);

        # Actualizamos la cita
        $cita->update(['notas' => $request->notas]);

        # Retornamos la cita
        return response()->json($cita);
    }

    public function actualizarEstado(Request $request, Cita $cita)
    {

        # Validamos la solicitud
        $request->validate(['estado' => 'required|in:pendiente,en_curso,completada,cancelada']);

        # Obtenemos el nuevo estado
        $nuevoEstado = $request->estado;

        $cita->load('prestacion.categoriaPrestacion');

        # Verificamos si la cita es una cirugía
        if ($cita->prestacion?->categoriaPrestacion?->nombre === 'Cirugia') {

            # Verificamos si la cita es en curso o completada
            if (in_array($nuevoEstado, ['en_curso', 'completada'])) {

                # Verificamos si la cita tiene un arsenalero
                $tieneArsenalero = $cita->equipoMedico()
                    ->whereHas('rol', function ($q) {
                        $q->where('nombre_interno', 'arsenalero');
                    })
                    ->exists();

                # Si la cita no tiene un arsenalero, retornamos un error
                if (!$tieneArsenalero) {
                    return response()->json([
                        'error' => 'Para iniciar o completar una cirugía, debe asignar al menos un arsenalero en el equipo médico.'
                    ], 422);
                }
            }
        }

        # Si el estado es completada y no tiene transacción, generamos una
        if ($nuevoEstado === 'completada' && !$cita->transaccion) {
            $cita->load('prestacion');

            # Obtenemos la mascota
            $mascota = Mascota::find($cita->mascota_id);

            # Calculamos el total de los insumos
            $totalCargos = CitaCargo::where('cita_id', $cita->id)
                ->whereNotNull('insumo_id')
                ->sum('subtotal');

            # Obtenemos el monto total de la cita
            $montoTotal = ($cita->prestacion ? $cita->prestacion->precio_base : 0) + $totalCargos;

            # Creamos la transacción
            Transaccion::create([
                'cita_id' => $cita->id,
                'cliente_id' => $mascota->cliente_id,
                'monto_total' => $montoTotal,
                'monto_pagado' => 0,
                'estado' => 'pendiente',
            ]);

            # Si el estado es cancelada y tiene transacción, anulamos la transacción
        } elseif ($nuevoEstado === 'cancelada') {
            if ($cita->transaccion) {
                $cita->transaccion->update(['estado' => 'anulado']);
            }
        }

        # Actualizamos la cita
        $cita->update(['estado' => $nuevoEstado]);

        # Retornamos la cita
        return response()->json($cita->load('transaccion'));
    }
}

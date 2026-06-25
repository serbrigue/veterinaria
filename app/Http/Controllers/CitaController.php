<?php

namespace App\Http\Controllers;

use App\Models\Cita;
use App\Models\Cliente;
use App\Models\Mascota;
use App\Models\CitaCargo;
use App\Models\Insumo;
use App\Models\Prestacion;
use App\Models\Transaccion;
use \Illuminate\Support\Carbon;
use App\Models\Sucursal;
use App\Models\Box;
use App\Models\Veterinario;
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
        if (auth()->user()->isAdmin() || auth()->user()->isVeterinario()) {
            $mascotas = Mascota::with('cliente.usuario', 'raza.especie')->get();
        } else {
            $mascotas = Mascota::where('cliente_id', auth()->user()->cliente?->id)->get();
        }

        $query = Cita::with(['mascota.cliente.usuario', 'veterinario.usuario', 'box', 'transaccion'])
            ->when($request->filled('mascota_id'), fn($q) => $q->where('mascota_id', $request->mascota_id))
            ->when($request->filled('veterinario_id'), fn($q) => $q->where('veterinario_id', $request->veterinario_id))
            ->when($request->filled('sucursal_id'), fn($q) => $q->whereHas('box', fn($b) => $b->where('sucursal_id', $request->sucursal_id)))
            ->when($request->filled('titulo'), fn($q) => $q->where('titulo', 'like', '%' . $request->titulo . '%'))
            ->when($request->filled('estado'), fn($q) => $q->where('estado', $request->estado));

        if (!auth()->user()->isAdmin() && !auth()->user()->isVeterinario()) {
            $clienteId = auth()->user()->cliente?->id;
            $query->whereHas('mascota', fn($q) => $q->where('cliente_id', $clienteId));

            if (!$request->filled('estado')) {
                $query->where('estado', '!=', 'cancelada');
            }
        }

        $citas = $query->orderBy('fecha_hora', 'desc')->paginate(15);

        $sucursales = Cache::remember('sucursales_full', now()->addMinutes(30), function() {
            return Sucursal::with(['veterinarios.usuario', 'boxes'])->orderBy('nombre')->get();
        });
        
        $prestaciones = Cache::remember('prestaciones_full', now()->addMinutes(30), function() {
            return Prestacion::with(['sucursal', 'especialidad'])->orderBy('nombre')->get();
        });
        
        $veterinarios = Cache::remember('veterinarios_simple', now()->addMinutes(30), function() {
            return Veterinario::all();
        });

        if ($request->wantsJson()) {
            return response()->json([
                'citas' => $citas,
                'mascotas' => $mascotas,
                'sucursales' => $sucursales,
                'prestaciones' => $prestaciones,
            ]);
        }

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
        /**
         * Intención de negocio:
         * Proveer el listado filtrado de citas para la API interna.
         * Sigue las mismas reglas de visibilidad y restricciones de rol que el listado web.
         */
        if (auth()->user()->isAdmin() || auth()->user()->isVeterinario()) {
            return Cita::with(['mascota.cliente.usuario', 'veterinario.usuario'])->get();
        }

        $clienteId = auth()->user()->cliente?->id;
        return Cita::whereHas('mascota', function ($query) use ($clienteId) {
            $query->where('cliente_id', $clienteId);
        })->with(['mascota.cliente.usuario', 'veterinario.usuario', 'box'])->get();
    }

    public function crear(GuardarCitaRequest $solicitud)
    {
        $data = $solicitud->validated();

        return DB::transaction(function () use ($data) {
            $horaTermino = Carbon::parse($data['fecha_hora'])->addMinutes(30);

            // Bloqueamos los recursos padre para evitar condiciones de carrera (doble reserva concurrente)
            Veterinario::where('id', $data['veterinario_id'])->lockForUpdate()->first();
            Box::where('id', $data['box_id'])->lockForUpdate()->first();

            $solapamientoCitasVeterinario = Cita::where('veterinario_id', $data['veterinario_id'])
                ->where('fecha_hora', '<', $horaTermino)
                ->where('hora_termino', '>', Carbon::parse($data['fecha_hora']))
                ->where('estado', '!=', 'cancelada')
                ->exists();

            $solapamientoCitasBox = Cita::where('box_id', $data['box_id'])
                ->where('fecha_hora', '<', $horaTermino)
                ->where('hora_termino', '>', Carbon::parse($data['fecha_hora']))
                ->where('estado', '!=', 'cancelada')
                ->exists();

            if ($solapamientoCitasVeterinario) {
                return response()->json(['error' => 'No se puede agendar la cita, el veterinario ya está ocupado en ese horario'], 409);
            }
            if ($solapamientoCitasBox) {
                return response()->json(['error' => 'No se puede agendar la cita, el box ya está ocupado en ese horario'], 409);
            }

            $cita = Cita::create([
                'titulo' => $data['titulo'],
                'descripcion' => $data['descripcion'],
                'fecha_hora' => $data['fecha_hora'],
                'hora_termino' => $horaTermino,
                'estado' => 'pendiente',
                'mascota_id' => $data['mascota_id'],
                'veterinario_id' => $data['veterinario_id'],
                'box_id' => $data['box_id'],
                'prestacion_id' => $data['prestacion_id'],
            ]);

            return response()->json($cita, 201);
        });
    }

    public function actualizar(ActualizarCitaRequest $solicitud, Cita $cita)
    {
        /**
         * Intención de negocio:
         * Modificar los datos de la cita médica agendada.
         * La verificación de propiedad está delegada al middleware 'can' de la policy.
         */
        $data = $solicitud->validated();

        return DB::transaction(function () use ($data, $cita) {
            $horaTermino = Carbon::parse($data['fecha_hora'])->addMinutes(30);

            // Bloqueamos los recursos padre para evitar condiciones de carrera
            Veterinario::where('id', $data['veterinario_id'])->lockForUpdate()->first();
            Box::where('id', $data['box_id'])->lockForUpdate()->first();

            $solapamientoCitasVeterinario = Cita::where('veterinario_id', $data['veterinario_id'])
                ->where('id', '!=', $cita->id)
                ->where('fecha_hora', '<', $horaTermino)
                ->where('hora_termino', '>', Carbon::parse($data['fecha_hora']))
                ->where('estado', '!=', 'cancelada')
                ->exists();

            $solapamientoCitasBox = Cita::where('box_id', $data['box_id'])
                ->where('id', '!=', $cita->id)
                ->where('fecha_hora', '<', $horaTermino)
                ->where('hora_termino', '>', Carbon::parse($data['fecha_hora']))
                ->where('estado', '!=', 'cancelada')
                ->exists();

            if ($solapamientoCitasVeterinario) {
                return response()->json(['error' => 'No se puede actualizar la cita, el veterinario ya está ocupado en ese horario'], 409);
            }
            if ($solapamientoCitasBox) {
                return response()->json(['error' => 'No se puede actualizar la cita, el box ya está ocupado en ese horario'], 409);
            }

            $cita->update(array_merge($data, ['hora_termino' => $horaTermino]));
            return response()->json($cita);
        });
    }

    public function horariosDisponibles(Request $request)
    {
        $request->validate([
            'fecha'          => 'required|date_format:Y-m-d',
            'veterinario_id' => 'required|exists:veterinarios,id',
            'box_id'         => 'required|exists:boxes,id',
        ]);

        $fecha    = $request->fecha;
        $duracion = 30; // minutos por slot

        // Traemos solo las columnas necesarias para la comparación de solapamiento
        $citasVet = Cita::where('veterinario_id', $request->veterinario_id)
            ->whereDate('fecha_hora', $fecha)
            ->where('estado', '==', 'pendiente')
            ->get(['fecha_hora', 'hora_termino']);

        $citasBox = Cita::where('box_id', $request->box_id)
            ->whereDate('fecha_hora', $fecha)
            ->where('estado', '==', 'pendiente')
            ->get(['fecha_hora', 'hora_termino']);

        // Genera todos los slots de 30 min entre $inicio y $fin con el tipo indicado
        $generarSlots = function (Carbon $inicio, Carbon $fin, string $tipo) use ($duracion, $citasVet, $citasBox) {
            $slots  = [];
            $cursor = $inicio->copy();

            while ($cursor->lt($fin)) {
                $slotFin = $cursor->copy()->addMinutes($duracion);

                // Solapamiento: la cita empieza antes de que termine el slot
                //               Y la cita termina después de que empieza el slot
                $ocupadoVet = $citasVet->some(
                    fn($c) => Carbon::parse($c->fecha_hora)->lt($slotFin)
                        && Carbon::parse($c->hora_termino)->gt($cursor)
                );
                $ocupadoBox = $citasBox->some(
                    fn($c) => Carbon::parse($c->fecha_hora)->lt($slotFin)
                        && Carbon::parse($c->hora_termino)->gt($cursor)
                );

                $slots[] = [
                    'hora'       => $cursor->format('H:i'),
                    'fecha_hora' => $cursor->toDateTimeString(),
                    'disponible' => !$ocupadoVet && !$ocupadoBox,
                    'tipo'       => $tipo,
                ];

                $cursor->addMinutes($duracion);
            }

            return $slots;
        };

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

    public function cancelar(Cita $cita)
    {

        if ($cita->estado === 'cancelada') {
            return response()->json(['mensaje' => 'La cita ya estaba cancelada'], 422);
        }

        $cita->update(['estado' => 'cancelada']);

        return response()->json(['mensaje' => 'Cita cancelada correctamente']);
    }

    public function detalle(Cita $cita)
    {
        $cita->load([
            'mascota.cliente.usuario',
            'veterinario.usuario',
            'box.sucursal',
            'transaccion'
        ]);

        $mascota = Mascota::with([
            'cliente.usuario',
            'raza.especie',
        ])->find($cita->mascota_id);

        // Cargos ya registrados para esta cita (prestaciones e insumos usados)
        $cargos = CitaCargo::where('cita_id', $cita->id)
            ->with(['prestacion', 'insumo'])
            ->get();

        // Insumos disponibles en la sucursal del veterinario asignado
        $insumosSucursal = [];
        if ($cita->veterinario && $cita->box?->sucursal_id) {
            $insumosSucursal = Insumo::where('sucursal_id', $cita->box->sucursal_id)
                ->where('stock_actual', '>', 0)
                ->orderBy('nombre')
                ->get(['id', 'nombre', 'precio_venta', 'stock_actual']);
        }

        return Inertia::render('Cita/Detalle', [
            'cita'            => $cita,
            'cargos'          => $cargos,
            'insumosSucursal' => $insumosSucursal,
            'mascota'         => $mascota,
            'prestacion' => $cita->prestacion,
        ]);
    }

    public function actualizarNotas(Request $request, Cita $cita)
    {
        $request->validate(['notas' => 'nullable|string']);

        $cita->update(['notas' => $request->notas]);

        return response()->json($cita);
    }

    public function actualizarEstado(Request $request, Cita $cita)
    {
        $request->validate(['estado' => 'required|in:pendiente,en_curso,completada,cancelada']);

        $nuevoEstado = $request->estado;

        // Si el estado es completada y no tiene transacción, generamos una
        if ($nuevoEstado === 'completada' && !$cita->transaccion) {
            $cita->load('prestacion');
            $mascota = \App\Models\Mascota::find($cita->mascota_id);

            // Calculamos el total de los insumos
            $totalCargos = CitaCargo::where('cita_id', $cita->id)
                ->whereNotNull('insumo_id')
                ->sum('subtotal');

            $montoTotal = ($cita->prestacion ? $cita->prestacion->precio_base : 0) + $totalCargos;

            Transaccion::create([
                'cita_id' => $cita->id,
                'cliente_id' => $mascota->cliente_id,
                'monto_total' => $montoTotal,
                'monto_pagado' => 0,
                'estado' => 'pendiente',
            ]);
        } elseif ($nuevoEstado === 'cancelada') {
            if ($cita->transaccion) {
                $cita->transaccion->update(['estado' => 'anulado']);
            }
        }

        $cita->update(['estado' => $nuevoEstado]);

        return response()->json($cita->load('transaccion'));
    }
}

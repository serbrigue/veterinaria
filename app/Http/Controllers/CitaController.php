<?php

namespace App\Http\Controllers;

use App\Models\Cita;
use App\Models\Cliente;
use App\Models\Mascota;
use \Illuminate\Support\Carbon;
use App\Models\Sucursal;
use App\Models\Box;
use App\Models\Veterinario;
use App\Http\Requests\GuardarCitaRequest;
use App\Http\Requests\ActualizarCitaRequest;
use Inertia\Inertia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CitaController extends Controller
{

    public function listado(Request $request)
    {
        if (auth()->user()->isAdmin() || auth()->user()->isVeterinario()) {
            $mascotas = Mascota::with('cliente.usuario', 'raza.especie')->get(); // Carga todas
        } else {
            $mascotas = Mascota::where('cliente_id', auth()->user()->cliente?->id)->get(); // Carga solo las suyas
        }

        if (auth()->user()->isAdmin() || auth()->user()->isVeterinario()) {
            $citas = Cita::with(['mascota.cliente.usuario', 'veterinario.usuario', 'box'])->get();
        } else {
            $clienteId = auth()->user()->cliente?->id;
            $citas = Cita::whereHas('mascota', function ($query) use ($clienteId) {
                $query->where('cliente_id', $clienteId);
            })->with(['mascota.cliente.usuario', 'veterinario.usuario', 'box'])->where('estado', '!=', 'cancelada')->get();
        }

        $sucursales = Sucursal::with(['veterinarios.usuario', 'boxes'])->orderBy('nombre')->get();

        if ($request->wantsJson()) {
            return response()->json([
                'citas' => $citas,
                'mascotas' => $mascotas,
                'sucursales' => $sucursales,
            ]);
        }

        return Inertia::render('Cita/Listado', [
            'citas' => $citas,
            'mascotas' => $mascotas,
            'sucursales' => $sucursales,
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
            ->where('estado', '!=', 'cancelada')
            ->get(['fecha_hora', 'hora_termino']);

        $citasBox = Cita::where('box_id', $request->box_id)
            ->whereDate('fecha_hora', $fecha)
            ->where('estado', '!=', 'cancelada')
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
        $cita->load(['mascota.cliente.usuario', 'veterinario.usuario', 'box.sucursal']);

        return Inertia::render('Cita/Detalle', [
            'cita' => $cita,
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

        $cita->update(['estado' => $request->estado]);

        return response()->json($cita);
    }
}

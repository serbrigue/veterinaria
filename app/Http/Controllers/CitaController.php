<?php

namespace App\Http\Controllers;

use App\Http\Requests\ActualizarCitaRequest;
use App\Http\Requests\GuardarCitaRequest;
use App\Models\BloqueoHorario;
use App\Models\Box;
use App\Models\Cita;
use App\Models\CitaCargo;
use App\Models\Insumo;
use App\Models\Mascota;
use App\Models\Prestacion;
use App\Models\Rol;
use App\Models\Sucursal;
use App\Models\Transaccion;
use App\Models\User;
use App\Models\Veterinario;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class CitaController extends Controller
{
    public function listado(Request $request)
    {
        // Verificamos si el usuario es administrador, veterinario o secretaria
        if (auth()->user()->isAdmin() || auth()->user()->isVeterinario() || auth()->user()->rol?->nombre_interno === 'secretaria') {
            // Si lo es, traemos todas las mascotas
            $mascotas = Mascota::with('cliente.usuario', 'raza.especie')->get();
        } else {
            // Si no, traemos solo las mascotas del cliente
            $mascotas = Mascota::where('cliente_id', auth()->user()->cliente?->id)->get();
        }

        // Filtros de citas, con eager loading

        $query = Cita::with(['mascota.cliente.usuario', 'veterinario.usuario', 'box', 'transaccion'])
            ->when($request->filled('mascota_id'), fn($q) => $q->where('mascota_id', $request->mascota_id))
            ->when($request->filled('veterinario_id'), fn($q) => $q->where('veterinario_id', $request->veterinario_id))
            ->when($request->filled('sucursal_id'), fn($q) => $q->whereHas('box', fn($b) => $b->where('sucursal_id', $request->sucursal_id)))
            ->when($request->filled('titulo'), fn($q) => $q->where('titulo', 'like', '%' . $request->titulo . '%'))
            ->when($request->filled('estado'), fn($q) => $q->where('estado', $request->estado));

        // Aplicamos restricciones según el rol del usuario
        if (auth()->user()->isAdmin()) {
            // Admin ve todo
        } else if (auth()->user()->isVeterinario()) {
            // Veterinario ve solo sus citas
            $query->where('veterinario_id', auth()->user()->veterinario?->id);
        } else if (auth()->user()->rol?->nombre_interno === 'secretaria') {
            // Secretaria ve las citas de su sucursal
            $query->whereHas('veterinario', fn($v) => $v->where('sucursal_id', auth()->user()->secretaria?->sucursal_id));
        } else {
            // Cliente ve solo sus citas
            $clienteId = auth()->user()->cliente?->id;
            $query->whereHas('mascota', fn($q) => $q->where('cliente_id', $clienteId));
        }

        // Aparte si no se especifica estado, no mostramos canceladas
        if (! $request->filled('estado')) {
            $query->where('estado', '!=', 'cancelada');
        }

        // Paginamos los resultados
        $citas = $query->orderBy('fecha_hora', 'desc')->paginate(15);

        // Traemos las sucursales con eager loading
        $sucursales = Cache::remember('sucursales_full', now()->addMinutes(30), function () {
            return Sucursal::with(['veterinarios.usuario', 'boxes'])->orderBy('nombre')->get();
        });

        // Traemos las prestaciones con eager loading
        $prestaciones = Cache::remember('prestaciones_full', now()->addMinutes(30), function () {
            return Prestacion::with(['sucursal', 'especialidad'])->orderBy('nombre')->get();
        });

        // Traemos los veterinarios con eager loading
        $veterinarios = Cache::remember('veterinarios_simple', now()->addMinutes(30), function () {
            return Veterinario::all();
        });

        // Si la solicitud es en formato JSON, devolvemos JSON
        if ($request->wantsJson()) {
            return response()->json([
                'citas' => $citas,
                'mascotas' => $mascotas,
                'sucursales' => $sucursales,
                'prestaciones' => $prestaciones,
            ]);
        }

        // Devolvemos la vista con los datos
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
        // Si es admin, traemos todas las citas
        if (auth()->user()->isAdmin()) {
            return Cita::with(['mascota.cliente.usuario', 'veterinario.usuario'])->get();
        }

        // Si es veterinario, traemos solo sus citas
        if (auth()->user()->isVeterinario()) {
            return Cita::where('veterinario_id', auth()->user()->veterinario?->id)
                ->with(['mascota.cliente.usuario', 'veterinario.usuario'])
                ->get();
        }

        // Si es secretaria, traemos las de su sucursal
        if (auth()->user()->rol?->nombre_interno === 'secretaria') {
            return Cita::whereHas('veterinario', fn($v) => $v->where('sucursal_id', auth()->user()->secretaria?->sucursal_id))
                ->with(['mascota.cliente.usuario', 'veterinario.usuario', 'box'])
                ->get();
        }

        // Si no es admin ni veterinario, traemos solo las citas del cliente
        $clienteId = auth()->user()->cliente?->id;

        // Traemos las citas con eager loading, si no hay cliente no traemos nada
        return Cita::whereHas('mascota', function ($query) use ($clienteId) {
            $query->where('cliente_id', $clienteId);
        })->with(['mascota.cliente.usuario', 'veterinario.usuario', 'box'])->get();
    }

    public function crear(GuardarCitaRequest $solicitud)
    {
        // Validamos la solicitud
        $data = $solicitud->validated();

        // Iniciamos una transacción para evitar condiciones de carrera
        return DB::transaction(function () use ($data) {

            // Obtenemos la hora de termino de la cita
            $horaTermino = Carbon::parse($data['fecha_hora'])->addMinutes(30);

            Veterinario::where('id', $data['veterinario_id'])->lockForUpdate()->first();

            // Verificamos si hay solapamiento de citas con el veterinario
            $solapamientoCitasVeterinario = Cita::where('veterinario_id', $data['veterinario_id'])
                ->where('fecha_hora', '<', $horaTermino)
                ->where('hora_termino', '>', Carbon::parse($data['fecha_hora']))
                ->where('estado', '!=', 'cancelada')
                ->exists();

            if ($solapamientoCitasVeterinario) {
                return response()->json(['error' => 'No se puede agendar la cita, el veterinario ya está ocupado en ese horario'], 409);
            }

            $cita = Cita::create([
                'titulo' => $data['titulo'],
                'descripcion' => $data['descripcion'],
                'fecha_hora' => $data['fecha_hora'],
                'hora_termino' => $horaTermino,
                'estado' => 'pendiente',
                'mascota_id' => $data['mascota_id'],
                'veterinario_id' => $data['veterinario_id'],
                'box_id' => null,
                'prestacion_id' => $data['prestacion_id'],
            ]);

            // Retornamos la cita
            return response()->json($cita, 201);
        });
    }

    public function actualizar(ActualizarCitaRequest $solicitud, Cita $cita)
    {

        // Validamos la solicitud
        $data = $solicitud->validated();

        // Iniciamos una transacción para evitar condiciones de carrera
        return DB::transaction(function () use ($data, $cita) {
            // Obtenemos la hora de termino de la cita
            $horaTermino = Carbon::parse($data['fecha_hora'])->addMinutes(30);
            $boxId = array_key_exists('box_id', $data) ? $data['box_id'] : $cita->box_id;

            if ($boxId) {
                $errorMsg = $this->verificarBox($boxId, $data['prestacion_id'], $cita->id, $data['fecha_hora'], $horaTermino);
                if ($errorMsg) {
                    $codigoError = str_contains($errorMsg, 'ocupado') ? 409 : 422;

                    return response()->json(['error' => $errorMsg], $codigoError);
                }
            }

            // Bloqueamos los recursos padre para evitar condiciones de carrera
            Veterinario::where('id', $data['veterinario_id'])->lockForUpdate()->first();

            // Verificamos si hay solapamiento de citas con el veterinario
            $solapamientoCitasVeterinario = Cita::where('veterinario_id', $data['veterinario_id'])
                ->where('id', '!=', $cita->id)
                ->where('fecha_hora', '<', $horaTermino)
                ->where('hora_termino', '>', Carbon::parse($data['fecha_hora']))
                ->where('estado', '!=', 'cancelada')
                ->exists();

            if ($solapamientoCitasVeterinario) {
                return response()->json(['error' => 'No se puede actualizar la cita, el veterinario ya está ocupado en ese horario'], 409);
            }

            $cita->update(array_merge($data, ['hora_termino' => $horaTermino, 'box_id' => $boxId]));

            // Retornamos la cita
            return response()->json($cita);
        });
    }

    private function verificarBox(
        int $boxId,
        int $prestacionId,
        int $citaIdExcluida,
        string $fechaHora,
        Carbon $horaTermino
    ): ?string {
        $box = Box::find($boxId);
        $prestacion = Prestacion::find($prestacionId);

        if ($box && $box->categoria_prestacion_id !== null && $prestacion && $box->categoria_prestacion_id !== $prestacion->categoria_prestacion_id) {
            return 'El box "' . $box->nombre . '" no es compatible con el tipo de prestación seleccionada.';
        }

        Box::where('id', $boxId)->lockForUpdate()->first();

        $solapamiento = Cita::where('box_id', $boxId)
            ->where('id', '!=', $citaIdExcluida)
            ->where('fecha_hora', '<', $horaTermino)
            ->where('hora_termino', '>', Carbon::parse($fechaHora))
            ->where('estado', '!=', 'cancelada')
            ->exists();

        if ($solapamiento) {
            return 'No se puede actualizar la cita, el box ya está ocupado en ese horario';
        }

        return null;
    }

    public function horariosDisponibles(Request $request)
    {
        // Validamos la solicitud
        $request->validate([
            'fecha' => 'required|date_format:Y-m-d',
            'veterinario_id' => 'required|exists:veterinarios,id',
        ]);

        $fecha = $request->fecha;
        $veterinario = Veterinario::findOrFail($request->veterinario_id);

        // Obtener el número del día de la semana de la fecha utilizando Carbon (1 Lunes a 7 Domingo)
        $diaSemana = Carbon::parse($fecha)->dayOfWeekIso;

        // Recuperar el horario personalizado o usar el valor predeterminado
        $horarioCustom = $veterinario->horario;
        $diaConfig = $this->resolverConfiguracionDia($horarioCustom, $diaSemana, $fecha);

        $citasVeterinario = Cita::where('veterinario_id', $request->veterinario_id)
            ->whereDate('fecha_hora', $fecha)
            ->where('estado', '!=', 'cancelada')
            ->get(['fecha_hora', 'hora_termino']);

        // Obtener bloqueos de emergencia que coincidan con la fecha seleccionada
        $bloqueos = BloqueoHorario::where('veterinario_id', $request->veterinario_id)
            ->whereDate('fecha_inicio', '<=', $fecha)
            ->whereDate('fecha_fin', '>=', $fecha)
            ->get();

        $normalSlots = [];
        if (! empty($diaConfig['normal']['activo']) && ! empty($diaConfig['normal']['inicio']) && ! empty($diaConfig['normal']['fin'])) {
            $inicioNormal = Carbon::parse($diaConfig['normal']['inicio']);
            $finNormal = Carbon::parse($diaConfig['normal']['fin']);
            $normalSlots = $this->generarSlotsHorarios(
                $fecha,
                $inicioNormal->hour,
                $inicioNormal->minute,
                $finNormal->hour,
                $finNormal->minute,
                'normal',
                $citasVeterinario,
                $bloqueos
            );
        }

        $urgenciaSlots = [];
        if (! empty($diaConfig['urgencia']['activo']) && ! empty($diaConfig['urgencia']['inicio']) && ! empty($diaConfig['urgencia']['fin'])) {
            $inicioUrgencia = Carbon::parse($diaConfig['urgencia']['inicio']);
            $finUrgencia = Carbon::parse($diaConfig['urgencia']['fin']);
            $urgenciaSlots = $this->generarSlotsHorarios(
                $fecha,
                $inicioUrgencia->hour,
                $inicioUrgencia->minute,
                $finUrgencia->hour,
                $finUrgencia->minute,
                'urgencia',
                $citasVeterinario,
                $bloqueos
            );
        }

        return response()->json([
            'normal' => $normalSlots,
            'urgencia' => $urgenciaSlots,
        ]);
    }

    // Genera los slots horarios para un tipo de slot específico (normal o urgencia)
    private function generarSlotsHorarios(

        string $fecha,
        int $horaInicio,
        int $minutoInicio,
        int $horaFin,
        int $minutoFin,
        string $tipoSlot,
        $citasVeterinario,
        $bloqueos
    ): array {
        $inicio = Carbon::parse($fecha)->setTime($horaInicio, $minutoInicio);
        $fin = Carbon::parse($fecha)->setTime($horaFin, $minutoFin);
        $slots = [];
        $cursor = $inicio->copy();

        while ($cursor->lt($fin)) {
            $slotFin = $cursor->copy()->addMinutes(30);

            // Verificar si el slot actual se encuentra ocupado por alguna cita
            $ocupadoVeterinario = $citasVeterinario->some(
                fn($cita) => Carbon::parse($cita->fecha_hora)->lt($slotFin)
                    && Carbon::parse($cita->hora_termino)->gt($cursor)
            );

            // Verificar si el slot actual se encuentra bloqueado por alguna emergencia
            $estaBloqueado = $bloqueos->some(function ($bloqueo) use ($fecha, $cursor, $slotFin) {
                // Si no tiene horas de inicio y fin, se bloquea el día completo
                if (is_null($bloqueo->hora_inicio) && is_null($bloqueo->hora_fin)) {
                    return true;
                }

                $bloqueoInicio = Carbon::parse($fecha . ' ' . $bloqueo->hora_inicio);
                $bloqueoFin = Carbon::parse($fecha . ' ' . $bloqueo->hora_fin);

                return $bloqueoInicio->lt($slotFin) && $bloqueoFin->gt($cursor);
            });

            $slots[] = [
                'hora' => $cursor->format('H:i'),
                'fecha_hora' => $cursor->toDateTimeString(),
                'disponible' => ! $ocupadoVeterinario && ! $estaBloqueado,
                'tipo' => $tipoSlot,
            ];

            $cursor->addMinutes(30);
        }

        return $slots;
    }

    private function resolverConfiguracionDia(?array $horario, int $diaSemana, string $fecha): array
    {
        if ($horario && is_array($horario) && count($horario) > 0) {
            $primerElemento = $horario[0];

            if (isset($primerElemento['dias'])) {
                return $this->buscarDiaEnPlanes($horario, $diaSemana, $fecha);
            }

            return $this->buscarDiaEnFormatoPlano($horario, $diaSemana);
        }

        return $this->configuracionDiaPorDefecto($diaSemana);
    }

    private function buscarDiaEnPlanes(array $planes, int $diaSemana, string $fecha): array
    {
        foreach ($planes as $plan) {
            $fechaInicio = $plan['fecha_inicio'] ?? null;
            $fechaFin = $plan['fecha_fin'] ?? null;

            if ($fechaInicio && $fechaFin && ($fecha < $fechaInicio || $fecha > $fechaFin)) {
                continue;
            }

            foreach ($plan['dias'] ?? [] as $dia) {
                if (isset($dia['dia']) && (int) $dia['dia'] === $diaSemana) {
                    return $dia;
                }
            }
        }

        return $this->configuracionDiaPorDefecto($diaSemana);
    }

    private function buscarDiaEnFormatoPlano(array $horario, int $diaSemana): array
    {
        foreach ($horario as $dia) {
            if (isset($dia['dia']) && (int) $dia['dia'] === $diaSemana) {
                return $dia;
            }
        }

        return $this->configuracionDiaPorDefecto($diaSemana);
    }

    private function configuracionDiaPorDefecto(int $diaSemana): array
    {
        $esFinSemana = in_array($diaSemana, [6, 7]);

        return [
            'dia' => $diaSemana,
            'normal' => [
                'activo' => ! $esFinSemana,
                'inicio' => '09:00',
                'fin' => '18:00',
            ],
            'urgencia' => [
                'activo' => ! $esFinSemana,
                'inicio' => '18:00',
                'fin' => '21:30',
            ],
        ];
    }

    public function cancelar(Request $request, Cita $cita)
    {

        // Verificamos si la cita ya está cancelada
        if ($cita->estado === 'cancelada') {
            return response()->json(['mensaje' => 'La cita ya estaba cancelada'], 422);
        }

        // Obtenemos el motivo de la cancelación
        $motivo = $request->input('motivo_cancelacion', 'Cancelada sin motivo especificado.');

        // Actualizamos la cita
        $cita->update([
            'estado' => 'cancelada',
            'notas' => $motivo,
        ]);

        // Retornamos la cita
        return response()->json(['mensaje' => 'Cita cancelada correctamente']);
    }

    public function detalle(Cita $cita)
    {

        // Cargamos las relaciones de la cita
        $cita->load([
            'mascota.cliente.usuario',
            'veterinario.usuario',
            'box.sucursal',
            'transaccion',
            'prestacion.categoriaPrestacion',
            'equipoMedico.usuario',
            'equipoMedico.rol',
        ]);

        // Obtenemos la mascota
        $mascota = Mascota::with([
            'cliente.usuario',
            'raza.especie',
        ])->find($cita->mascota_id);

        // Obtenemos los cargos registrados para esta cita
        $cargos = CitaCargo::where('cita_id', $cita->id)
            ->with(['prestacion', 'insumo'])
            ->get();

        // Obtenemos los insumos disponibles en la sucursal del veterinario asignado
        $insumosSucursal = [];
        if ($cita->veterinario && $cita->box?->sucursal_id) {
            $insumosSucursal = Insumo::where('sucursal_id', $cita->box->sucursal_id)
                ->where('stock_actual', '>', 0)
                ->orderBy('nombre')
                ->get(['id', 'nombre', 'precio_venta', 'stock_actual']);
        }

        // Obtenemos el personal médico adicional si la cita es una cirugía
        $rolesMedicos = [];
        $usuariosMedicos = [];

        // Si la cita es una cirugía, obtenemos el personal médico adicional
        if ($cita->prestacion?->categoriaPrestacion?->nombre === 'Cirugia') {
            // Obtenemos los roles médicos
            $rolesMedicos = Rol::whereIn('nombre_interno', ['anestesista', 'arsenalero', 'tens', 'enfermero'])->get();
            // Obtenemos los usuarios médicos
            $usuariosMedicos = User::whereIn('rol_id', $rolesMedicos->pluck('id'))->with('rol')->orderBy('name')->get();
        }

        // Obtenemos los boxes compatibles de la sucursal de la prestación de la cita
        $boxes = [];

        // Si hay prestación
        if ($cita->prestacion) {

            // Obtenemos la sucursal de la prestación
            $sucursalId = $cita->prestacion->sucursal_id;
            // Obtenemos la categoría de la prestación
            $categoriaPrestacionId = $cita->prestacion->categoria_prestacion_id;

            // Obtenemos los boxes de la sucursal que tienen la misma categoría de prestación
            $boxes = Box::where('sucursal_id', $sucursalId)
                ->where('categoria_prestacion_id', $categoriaPrestacionId)
                ->orderBy('nombre')
                ->get(['id', 'nombre', 'categoria_prestacion_id']);
        }

        // Retornamos la vista de detalle
        return Inertia::render('Cita/Detalle', [
            'cita' => $cita,
            'cargos' => $cargos,
            'insumosSucursal' => $insumosSucursal,
            'mascota' => $mascota,
            'prestacion' => $cita->prestacion,
            'rolesMedicos' => $rolesMedicos,
            'usuariosMedicos' => $usuariosMedicos,
            'boxes' => $boxes,
        ]);
    }

    public function actualizarNotas(Request $request, Cita $cita)
    {

        // Validamos la solicitud
        $request->validate(['notas' => 'nullable|string']);

        // Actualizamos la cita
        $cita->update(['notas' => $request->notas]);

        // Retornamos la cita
        return response()->json($cita);
    }

    public function actualizarEstado(Request $request, Cita $cita)
    {

        // Validamos la solicitud
        $request->validate(['estado' => 'required|in:pendiente,en_curso,completada,cancelada']);

        // Obtenemos el nuevo estado
        $nuevoEstado = $request->estado;

        $cita->load('prestacion.categoriaPrestacion');

        if ($errorBox = $this->validarBoxAsignado($cita, $nuevoEstado)) {
            return response()->json(['error' => $errorBox], 422);
        }

        // Validamos si la cita es una cirugía y tiene el personal necesario
        if ($errorCirugia = $this->validarPersonalCirugia($cita, $nuevoEstado)) {
            return response()->json(['error' => $errorCirugia], 422);
        }

        // Procesamos la creación o anulación de la transacción
        $this->procesarTransaccionCita($cita, $nuevoEstado);

        // Actualizamos la cita
        $cita->update(['estado' => $nuevoEstado]);

        // Retornamos la cita
        return response()->json($cita->load('transaccion'));
    }

    private function validarBoxAsignado(Cita $cita, string $nuevoEstado): ?string
    {
        if (in_array($nuevoEstado, ['en_curso', 'completada']) && ! $cita->box_id) {
            return 'Debe asignar un box a la cita antes de iniciarla o completarla.';
        }

        return null;
    }

    private function validarPersonalCirugia(Cita $cita, string $nuevoEstado): ?string
    {
        if ($cita->prestacion?->categoriaPrestacion?->nombre === 'Cirugia') {
            if (in_array($nuevoEstado, ['en_curso', 'completada'])) {
                $tieneArsenalero = $cita->equipoMedico()
                    ->whereHas('rol', function ($q) {
                        $q->where('nombre_interno', 'arsenalero');
                    })
                    ->exists();

                if (! $tieneArsenalero) {
                    return 'Para iniciar o completar una cirugía, debe asignar al menos un arsenalero en el equipo médico.';
                }
            }
        }

        return null;
    }

    private function procesarTransaccionCita(Cita $cita, string $nuevoEstado): void
    {
        if ($nuevoEstado === 'completada' && ! $cita->transaccion) {
            $cita->load('prestacion');
            $mascota = Mascota::find($cita->mascota_id);

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
    }

    public function agendaSecretaria()
    {
        if (auth()->user()->rol->nombre_interno !== 'secretaria') {
            abort(403, 'Acceso exclusivo para el personal de secretaría.');
        }

        $citas = Cita::with(['mascota.cliente.usuario', 'veterinario.usuario', 'box', 'prestacion'])
            ->where('fecha_hora', '>=', Carbon::today())
            ->orderBy('fecha_hora', 'asc')
            ->get();

        $mascotas = Mascota::with('cliente.usuario', 'raza.especie')->get();
        $sucursales = Sucursal::with(['veterinarios.usuario', 'boxes'])->orderBy('nombre')->get();
        $prestaciones = Prestacion::with(['sucursal', 'especialidad'])->orderBy('nombre')->get();
        $veterinarios = Veterinario::with('usuario')->get();

        return Inertia::render('Secretaria/Calendario', [
            'citas' => $citas,
            'mascotas' => $mascotas,
            'sucursales' => $sucursales,
            'prestaciones' => $prestaciones,
            'veterinarios' => $veterinarios,
        ]);
    }
}

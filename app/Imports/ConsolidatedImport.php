<?php

namespace App\Imports;

use App\Models\Cita;
use App\Models\Cliente;
use App\Models\Mascota;
use App\Models\Raza;
use App\Models\Rol;
use App\Models\User;
use App\Models\Veterinario;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\ToCollection;

class ConsolidatedImport implements ToCollection
{
    protected array $mapping;

    protected array $modules;

    protected ?int $clienteRolId;

    public array $descartados = [];

    public array $headersOriginales = [];

    public function __construct(array $mapping, array $modules)
    {
        $this->mapping = $mapping;
        $this->modules = $modules;

        // Caché del Rol Cliente para evitar queries repetitivas
        $rol = Rol::where('nombre_interno', 'cliente')->first();
        $this->clienteRolId = $rol ? $rol->id : null;
    }

    public function collection(Collection $rows)
    {
        if ($rows->isEmpty()) {
            return;
        }

        // Fila 1 son headers. Extraemos y creamos un mapa "Nombre Header" => "Índice (0, 1, 2...)"
        $headers = $rows->first()->toArray();
        $headers = array_map('trim', $headers);
        $this->headersOriginales = $headers;
        $headerToIndex = [];
        foreach ($headers as $idx => $header) {
            $headerToIndex[$header] = $idx;
        }

        foreach ($rows as $index => $row) {
            if ($index === 0) {
                continue; // Saltamos la fila de encabezados
            }

            // El índice en Excel es $index + 1
            $currentRow = $index + 1;

            try {
                // VARIABLES PARA RETENER IDs
                $clienteId = null;
                $mascotaId = null;

                // ==========================================
                // RF-04 y RF-05: PROCESAR CLIENTES
                // ==========================================
                if (! empty($this->modules['clientes'])) {
                    $emailColStr = $this->mapping['cliente_email'] ?? null;
                    $nombreColStr = $this->mapping['cliente_nombre'] ?? null;
                    $telefonoColStr = $this->mapping['cliente_telefono'] ?? null;
                    $direccionColStr = $this->mapping['cliente_direccion'] ?? null;

                    $emailIndex = $emailColStr ? ($headerToIndex[$emailColStr] ?? null) : null;
                    $nombreIndex = $nombreColStr ? ($headerToIndex[$nombreColStr] ?? null) : null;
                    $telefonoIndex = $telefonoColStr ? ($headerToIndex[$telefonoColStr] ?? null) : null;
                    $direccionIndex = $direccionColStr ? ($headerToIndex[$direccionColStr] ?? null) : null;

                    $email = $emailIndex !== null ? $row[$emailIndex] : null;
                    $nombre = $nombreIndex !== null && ! empty($row[$nombreIndex]) ? $row[$nombreIndex] : 'Cliente Sin Nombre';

                    if ($email) {
                        // Upsert User
                        $user = User::updateOrCreate(
                            ['email' => $email],
                            [
                                'name' => $nombre,
                                // Si es nuevo, generamos una password aleatoria
                                'password' => Hash::make(Str::random(12)),
                                'rol_id' => $this->clienteRolId,
                            ]
                        );

                        // Upsert Cliente
                        $cliente = Cliente::updateOrCreate(
                            ['user_id' => $user->id],
                            [
                                'telefono' => $telefonoIndex !== null ? $row[$telefonoIndex] : null,
                                'direccion' => $direccionIndex !== null ? $row[$direccionIndex] : null,
                            ]
                        );

                        $clienteId = $cliente->id;
                    } else {
                        // Fallback cliente no identificado (ID 999)
                        $clienteId = $this->obtenerClienteComodin();
                    }
                } else {
                    $clienteId = $this->obtenerClienteComodin();
                }

                // ==========================================
                // RF-04 y RF-05: PROCESAR MASCOTAS
                // ==========================================
                if (! empty($this->modules['mascotas'])) {
                    $nombreMascotaColStr = $this->mapping['mascota_nombre'] ?? null;
                    $razaColStr = $this->mapping['mascota_raza'] ?? null;

                    $nombreMascotaIndex = $nombreMascotaColStr ? ($headerToIndex[$nombreMascotaColStr] ?? null) : null;
                    $razaIndex = $razaColStr ? ($headerToIndex[$razaColStr] ?? null) : null;

                    $nombreMascota = $nombreMascotaIndex !== null ? $row[$nombreMascotaIndex] : null;
                    $razaNombre = $razaIndex !== null ? $row[$razaIndex] : null;

                    if ($nombreMascota && $clienteId) {
                        $razaId = $this->resolverRaza($razaNombre);

                        $mascotaId = $this->resolverMascota(
                            $nombreMascota,
                            $clienteId,
                            $razaId
                        );
                    }
                }

                // ==========================================
                // RF-04 y RF-06: PROCESAR CITAS
                // ==========================================
                if (! empty($this->modules['citas'])) {
                    $fechaHoraColStr = $this->mapping['cita_fecha_hora'] ?? null;
                    $veterinarioColStr = $this->mapping['cita_veterinario'] ?? null;
                    $tituloColStr = $this->mapping['cita_titulo'] ?? null;
                    $valorColStr = $this->mapping['cita_valor'] ?? null;
                    $estadoTransaccionColStr = $this->mapping['cita_estado_transaccion'] ?? null;
                    $cargoColStr = $this->mapping['cita_cargo'] ?? null;

                    $fechaHoraIndex = $fechaHoraColStr ? ($headerToIndex[$fechaHoraColStr] ?? null) : null;
                    $veterinarioIndex = $veterinarioColStr ? ($headerToIndex[$veterinarioColStr] ?? null) : null;
                    $tituloIndex = $tituloColStr ? ($headerToIndex[$tituloColStr] ?? null) : null;
                    $valorIndex = $valorColStr ? ($headerToIndex[$valorColStr] ?? null) : null;
                    $estadoTransaccionIndex = $estadoTransaccionColStr ? ($headerToIndex[$estadoTransaccionColStr] ?? null) : ($headerToIndex['Estado Transaccion'] ?? null);
                    $cargoIndex = $cargoColStr ? ($headerToIndex[$cargoColStr] ?? null) : ($headerToIndex['Cargos'] ?? null);

                    $fechaHoraRaw = $fechaHoraIndex !== null ? $row[$fechaHoraIndex] : null;
                    $veterinarioNombre = $veterinarioIndex !== null ? $row[$veterinarioIndex] : null;
                    $titulo = $tituloIndex !== null && ! empty($row[$tituloIndex]) ? $row[$tituloIndex] : 'Cita Importada';
                    $valorRaw = $valorIndex !== null ? $row[$valorIndex] : null;
                    $estadoTransaccionRaw = $estadoTransaccionIndex !== null ? $row[$estadoTransaccionIndex] : null;
                    $cargoRaw = $cargoIndex !== null ? $row[$cargoIndex] : null;

                    if ($fechaHoraRaw && $mascotaId) {
                        // Parsear fecha, detectando si Excel la envió como número serial o como texto
                        if (is_numeric($fechaHoraRaw)) {
                            $fechaHora = Carbon::instance(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($fechaHoraRaw));
                        } else {
                            $fechaHora = Carbon::parse($fechaHoraRaw);
                        }

                        $horaTermino = (clone $fechaHora)->addMinutes(30);

                        // Limpiamos el string para quedarnos solo con el número (por si trae "$", letras o espacios)
                        $valorLimpio = floatval(preg_replace('/[^0-9.]/', '', (string) $valorRaw));
                        $estadoTransaccionStr = $estadoTransaccionRaw ? strtolower(trim($estadoTransaccionRaw)) : null;

                        // Lógica de Estados Dinámicos
                        $estado = 'pendiente'; // Estado por defecto
                        $sinTransaccion = empty($estadoTransaccionStr) && $valorLimpio == 0;

                        if ($sinTransaccion) {
                            // Si no hay transacción y la cita es para antes de hoy, se cancela
                            if ($fechaHora->isBefore(Carbon::today())) {
                                $estado = 'cancelada';
                            }
                        } else {
                            // Si hay transacción pagada o valor > 0
                            if ($estadoTransaccionStr === 'pagado' || $valorLimpio > 0) {
                                $estado = 'completada';
                            }
                        }

                        // Fallback de veterinario
                        $veterinarioId = $this->resolverVeterinario($veterinarioNombre);

                        // Prevención de duplicados exactos (re-importación)
                        $existeCita = Cita::where('fecha_hora', $fechaHora->toDateTimeString())
                            ->where('veterinario_id', $veterinarioId)
                            ->where('mascota_id', $mascotaId)
                            ->exists();

                        if ($existeCita) {
                            throw new Exception("Duplicado exacto: La cita ya existe para este paciente, veterinario y fecha.");
                        }

                        // Validación de solapamiento solo para citas futuras (pendientes)
                        if ($estado === 'pendiente') {
                            $solapamientoCitasVeterinario = Cita::where('veterinario_id', $veterinarioId)
                                ->where('fecha_hora', '<', $horaTermino)
                                ->where('hora_termino', '>', $fechaHora)
                                ->where('estado', '!=', 'cancelada')
                                ->exists();

                            if ($solapamientoCitasVeterinario) {
                                throw new Exception("El veterinario ya está ocupado en el horario de {$fechaHora->format('Y-m-d H:i')}");
                            }
                        }

                        $cita = Cita::create([
                            'titulo' => $titulo,
                            'descripcion' => 'Cita importada desde archivo Excel.',
                            'fecha_hora' => $fechaHora->toDateTimeString(),
                            'hora_termino' => $horaTermino->toDateTimeString(),
                            'estado' => $estado,
                            'veterinario_id' => $veterinarioId,
                            'mascota_id' => $mascotaId,
                            'prestacion_id' => $this->obtenerPrestacionComodin(),
                        ]);

                        // Crear la transacción si corresponde
                        if ($valorLimpio > 0 || in_array($estadoTransaccionStr, ['pendiente', 'abonado', 'pagado', 'anulado'])) {
                            $estadoTransaccionNuevo = 'pendiente';
                            if (in_array($estadoTransaccionStr, ['pendiente', 'abonado', 'pagado', 'anulado'])) {
                                $estadoTransaccionNuevo = $estadoTransaccionStr;
                            } elseif ($valorLimpio > 0) {
                                $estadoTransaccionNuevo = 'pagado';
                            }

                            \App\Models\Transaccion::create([
                                'cita_id' => $cita->id,
                                'cliente_id' => $clienteId,
                                'monto_total' => $valorLimpio,
                                'monto_pagado' => $estadoTransaccionNuevo === 'pagado' ? $valorLimpio : 0,
                                'estado' => $estadoTransaccionNuevo,
                                'fecha_pago' => ($estadoTransaccionNuevo === 'pagado') ? $fechaHora->toDateTimeString() : null,
                            ]);
                        }

                        // Crear los cargos si corresponden
                        if ($cargoRaw) {
                            $nombresCargos = array_map('trim', explode(',', $cargoRaw));
                            foreach ($nombresCargos as $nombreCargo) {
                                if (empty($nombreCargo)) {
                                    continue;
                                }

                                $prestacionEncontrada = \App\Models\Prestacion::whereRaw('LOWER(nombre) LIKE ?', ['%' . strtolower($nombreCargo) . '%'])->first();
                                if ($prestacionEncontrada) {
                                    \App\Models\CitaCargo::create([
                                        'cita_id' => $cita->id,
                                        'prestacion_id' => $prestacionEncontrada->id,
                                        'insumo_id' => null,
                                        'cantidad' => 1,
                                        'precio_unitario' => $prestacionEncontrada->precio_base,
                                        'subtotal' => $prestacionEncontrada->precio_base,
                                        'pago_vet' => $prestacionEncontrada->comision_vet ?? 0,
                                    ]);
                                    continue;
                                }

                                $insumoEncontrado = \App\Models\Insumo::whereRaw('LOWER(nombre) LIKE ?', ['%' . strtolower($nombreCargo) . '%'])->first();
                                if ($insumoEncontrado) {
                                    \App\Models\CitaCargo::create([
                                        'cita_id' => $cita->id,
                                        'prestacion_id' => null,
                                        'insumo_id' => $insumoEncontrado->id,
                                        'cantidad' => 1,
                                        'precio_unitario' => $insumoEncontrado->precio_venta,
                                        'subtotal' => $insumoEncontrado->precio_venta,
                                        'pago_vet' => 0,
                                    ]);
                                    continue;
                                }
                            }
                        }
                    }
                }
            } catch (Exception $e) {
                // RNF-03: Capturamos cualquier error (ej. cita ocupada, fecha inválida)
                // y agregamos la fila a descartados con el motivo.
                $filaDatos = $row->toArray();
                // Añadimos el motivo de fallo al final de la fila
                $filaDatos[] = $e->getMessage();
                $this->descartados[] = $filaDatos;
            }
        }
    }

    /**
     * RF-06: Resuelve la raza o asigna un comodín
     */
    private function resolverRaza(?string $nombreRaza): ?int
    {
        if (! $nombreRaza || mb_strtolower(trim($nombreRaza)) === 'no especificada') {
            return $this->obtenerRazaComodin();
        }

        $raza = Raza::whereRaw('LOWER(nombre) LIKE ?', ['%' . mb_strtolower(trim($nombreRaza)) . '%'])->first();
        if ($raza) {
            return $raza->id;
        }

        return $this->obtenerRazaComodin();
    }

    private function obtenerRazaComodin(): ?int
    {
        $especie = \App\Models\Especie::find(999);
        if (!$especie) {
            \App\Models\Especie::insert([
                'id' => 999,
                'nombre' => 'No Especificada',
                'descripcion' => 'Generada automáticamente por importación',
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }

        $raza = Raza::find(999);
        if (!$raza) {
            Raza::insert([
                'id' => 999,
                'nombre' => 'No Especificada',
                'especie_id' => 999,
                'descripcion' => 'Generada automáticamente por importación',
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }

        return 999;
    }

    private function obtenerClienteComodin(): int
    {
        $user = User::find(999);
        if (!$user) {
            User::insert([
                'id' => 999,
                'name' => 'Cliente No Identificado',
                'email' => 'no-identificado@sistema.com',
                'password' => Hash::make(Str::random(12)),
                'rol_id' => $this->clienteRolId ?? 1,
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }

        $cliente = Cliente::find(999);
        if (!$cliente) {
            Cliente::insert([
                'id' => 999,
                'user_id' => 999,
                'telefono' => '000000',
                'direccion' => 'Sin dirección',
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }

        return 999;
    }

    /**
     * Busca una mascota existente por nombre y cliente.
     * Si existe, actualiza la raza cuando el import trae una más específica.
     * Si no existe, la crea con datos mínimos.
     */
    private function resolverMascota(
        string $nombre,
        int $clienteId,
        ?int $razaId
    ): int {
        $mascota = Mascota::where('cliente_id', $clienteId)
            ->whereRaw('LOWER(nombre) = ?', [mb_strtolower($nombre)])
            ->first();

        if ($mascota) {
            $this->actualizarRazaSiMejora($mascota, $razaId);

            return $mascota->id;
        }

        $nueva = Mascota::create([
            'nombre' => $nombre,
            'cliente_id' => $clienteId,
            'raza_id' => $razaId,
            'descripcion' => 'Mascota importada',
            'sexo' => 'Desconocido',
            'color' => 'No Especificado',
            'esterilizado' => false,
        ]);

        return $nueva->id;
    }

    /**
     * Actualiza la raza de una mascota solo si la actual es
     * el comodín (999) y la importada es una raza real.
     */
    private function actualizarRazaSiMejora(
        Mascota $mascota,
        ?int $razaId
    ): void {
        $razaComodinId = 999;
        $tieneComodin = $mascota->raza_id === $razaComodinId
            || $mascota->raza_id === null;
        $importTraeRazaReal = $razaId !== null
            && $razaId !== $razaComodinId;

        if ($tieneComodin && $importTraeRazaReal) {
            $mascota->update(['raza_id' => $razaId]);
        }
    }

    private function resolverVeterinario(?string $nombre): int
    {
        if ($nombre) {
            // Buscar veterinario por nombre de usuario
            $user = User::whereHas('veterinario')->where('name', 'like', "%{$nombre}%")->first();

            if ($user && $user->veterinario) {
                return $user->veterinario->id;
            }
        }

        // Fallback: Asignar al primer veterinario disponible en el sistema
        $primerVet = Veterinario::first();
        if ($primerVet) {
            return $primerVet->id;
        }

        throw new Exception('El sistema requiere al menos un Veterinario registrado para importar citas.');
    }

    /**
     * Obtiene una prestación por defecto para satisfacer el constraint
     */
    private function obtenerPrestacionComodin(): int
    {
        $prestacion = \App\Models\Prestacion::first();
        if ($prestacion) {
            return $prestacion->id;
        }

        throw new Exception('El sistema requiere al menos una Prestación registrada para importar citas.');
    }
}

<?php

namespace App\Imports;

use App\Models\Cliente;
use App\Models\User;
use App\Models\Rol;
use App\Models\Mascota;
use App\Models\Raza;
use App\Models\Cita;
use App\Models\Veterinario;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\ToCollection;
use Exception;
use Carbon\Carbon;

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

    /**
     * @param Collection $rows
     */
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
                if (!empty($this->modules['clientes'])) {
                    $emailColStr = $this->mapping['cliente_email'] ?? null;
                    $nombreColStr = $this->mapping['cliente_nombre'] ?? null;
                    $telefonoColStr = $this->mapping['cliente_telefono'] ?? null;
                    $direccionColStr = $this->mapping['cliente_direccion'] ?? null;

                    $emailIndex = $emailColStr ? ($headerToIndex[$emailColStr] ?? null) : null;
                    $nombreIndex = $nombreColStr ? ($headerToIndex[$nombreColStr] ?? null) : null;
                    $telefonoIndex = $telefonoColStr ? ($headerToIndex[$telefonoColStr] ?? null) : null;
                    $direccionIndex = $direccionColStr ? ($headerToIndex[$direccionColStr] ?? null) : null;

                    $email = $emailIndex !== null ? $row[$emailIndex] : null;
                    $nombre = $nombreIndex !== null && !empty($row[$nombreIndex]) ? $row[$nombreIndex] : 'Cliente Sin Nombre';

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
                    }
                }

                // ==========================================
                // RF-04 y RF-05: PROCESAR MASCOTAS
                // ==========================================
                if (!empty($this->modules['mascotas'])) {
                    $nombreMascotaColStr = $this->mapping['mascota_nombre'] ?? null;
                    $razaColStr = $this->mapping['mascota_raza'] ?? null;

                    $nombreMascotaIndex = $nombreMascotaColStr ? ($headerToIndex[$nombreMascotaColStr] ?? null) : null;
                    $razaIndex = $razaColStr ? ($headerToIndex[$razaColStr] ?? null) : null;

                    $nombreMascota = $nombreMascotaIndex !== null ? $row[$nombreMascotaIndex] : null;
                    $razaNombre = $razaIndex !== null ? $row[$razaIndex] : null;

                    if ($nombreMascota && $clienteId) {
                        // RF-06: Excepción Relacional - Fallback de Raza
                        $razaId = $this->resolverRaza($razaNombre);

                        $mascota = Mascota::firstOrCreate(
                            [
                                'nombre' => $nombreMascota,
                                'raza_id' => $razaId,
                                'cliente_id' => $clienteId,
                            ],
                            [
                                'descripcion' => 'Mascota importada',
                                'sexo' => 'Desconocido',
                                'color' => 'No Especificado',
                                'esterilizado' => false,
                            ]
                        );

                        $mascotaId = $mascota->id;
                    }
                }

                // ==========================================
                // RF-04 y RF-06: PROCESAR CITAS
                // ==========================================
                if (!empty($this->modules['citas'])) {
                    $fechaHoraColStr = $this->mapping['cita_fecha_hora'] ?? null;
                    $veterinarioColStr = $this->mapping['cita_veterinario'] ?? null;
                    $tituloColStr = $this->mapping['cita_titulo'] ?? null;
                    $valorColStr = $this->mapping['cita_valor'] ?? null;

                    $fechaHoraIndex = $fechaHoraColStr ? ($headerToIndex[$fechaHoraColStr] ?? null) : null;
                    $veterinarioIndex = $veterinarioColStr ? ($headerToIndex[$veterinarioColStr] ?? null) : null;
                    $tituloIndex = $tituloColStr ? ($headerToIndex[$tituloColStr] ?? null) : null;
                    $valorIndex = $valorColStr ? ($headerToIndex[$valorColStr] ?? null) : null;

                    $fechaHoraRaw = $fechaHoraIndex !== null ? $row[$fechaHoraIndex] : null;
                    $veterinarioNombre = $veterinarioIndex !== null ? $row[$veterinarioIndex] : null;
                    $titulo = $tituloIndex !== null && !empty($row[$tituloIndex]) ? $row[$tituloIndex] : 'Cita Importada';
                    $valorRaw = $valorIndex !== null ? $row[$valorIndex] : null;

                    if ($fechaHoraRaw && $mascotaId) {
                        // Parsear fecha, detectando si Excel la envió como número serial o como texto
                        if (is_numeric($fechaHoraRaw)) {
                            $fechaHora = Carbon::instance(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($fechaHoraRaw));
                        } else {
                            $fechaHora = Carbon::parse($fechaHoraRaw);
                        }

                        $horaTermino = (clone $fechaHora)->addMinutes(30);

                        // Lógica de Estados Dinámicos
                        $estado = 'pendiente'; // Aún no pasa la fecha
                        if ($fechaHora->isPast()) {
                            // Limpiamos el string para quedarnos solo con el número (por si trae "$", letras o espacios)
                            $valorLimpio = floatval(preg_replace('/[^0-9.]/', '', (string)$valorRaw));
                            
                            if ($valorLimpio > 0) {
                                $estado = 'completada'; // Pasó la fecha y pagó
                            } else {
                                $estado = 'cancelada'; // Pasó la fecha y NO hay valor
                            }
                        }

                        // Fallback de veterinario
                        $veterinarioId = $this->resolverVeterinario($veterinarioNombre);

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

                        Cita::create([
                            'titulo' => $titulo,
                            'descripcion' => 'Cita importada desde archivo Excel.',
                            'fecha_hora' => $fechaHora->toDateTimeString(),
                            'hora_termino' => $horaTermino->toDateTimeString(),
                            'estado' => $estado,
                            'veterinario_id' => $veterinarioId,
                            'mascota_id' => $mascotaId,
                            'prestacion_id' => $this->obtenerPrestacionComodin(),
                        ]);
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
        if (!$nombreRaza) {
            return $this->obtenerRazaComodin();
        }

        $raza = Raza::where('nombre', 'like', "%{$nombreRaza}%")->first();
        if ($raza) {
            return $raza->id;
        }

        return $this->obtenerRazaComodin();
    }

    /**
     * Obtiene una raza por defecto para evitar fallos de integridad
     */
    private function obtenerRazaComodin(): ?int
    {
        $especie = \App\Models\Especie::firstOrCreate(
            ['nombre' => 'No Especificada'],
            ['descripcion' => 'Generada automáticamente por importación']
        );

        $raza = Raza::firstOrCreate(
            ['nombre' => 'No Especificada'],
            [
                'descripcion' => 'Generada automáticamente por importación',
                'especie_id' => $especie->id,
            ]
        );
        return $raza->id;
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

        throw new Exception("El sistema requiere al menos un Veterinario registrado para importar citas.");
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

        throw new Exception("El sistema requiere al menos una Prestación registrada para importar citas.");
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Modelo PagoVeterinario
 *
 * Representa a la tabla `pago_veterinarios` en la base de datos.
 * Función: Gestiona el registro de pagos, liquidaciones o comisiones entregadas a los veterinarios.
 */
class PagoVeterinario extends Model
{
    // Atributos
    protected $fillable = [
        'veterinario_id',
        'usuario_id',
        'mes',
        'anio',
        'monto_total',
        'estado',
    ];

    // Relaciones

    // Relación con veterinario
    public function veterinario()
    {
        return $this->belongsTo(Veterinario::class);
    }

    public function comisionCalculada()
    {
        // 1. Determinar si el usuario asociado es Veterinario o Personal de Apoyo
        $usuario = $this->usuario()->with('rol')->first();
        $esVeterinario = $usuario && $usuario->rol?->nombre_interno === 'veterinario';

        if ($esVeterinario) {
            // Citas completadas donde fue el veterinario principal y la transacción fue pagada en el mes/año
            $citas = Cita::where('veterinario_id', $this->veterinario_id)
                ->where('estado', 'completada')
                ->whereHas('transaccion', function ($t) {
                    $t->where('estado', 'pagado')
                        ->whereMonth('fecha_pago', $this->mes)
                        ->whereYear('fecha_pago', $this->anio);
                })
                ->with('prestacion')
                ->get();

            return $citas->sum(function ($cita) {
                $precio = $cita->prestacion?->precio_base ?? 0;
                $porcentaje = $cita->prestacion?->comision_vet ?? 0;
                return ($precio * $porcentaje) / 100;
            });
        } else {
            // Citas completadas donde formó parte del equipo de apoyo
            $citas = Cita::whereHas('equipoMedico', function ($em) {
                $em->where('usuario_id', $this->usuario_id);
            })
                ->where('estado', 'completada')
                ->whereHas('transaccion', function ($t) {
                    $t->where('estado', 'pagado')
                        ->whereMonth('fecha_pago', $this->mes)
                        ->whereYear('fecha_pago', $this->anio);
                })
                ->with('prestacion')
                ->get();

            return $citas->sum(function ($cita) {
                $precio = $cita->prestacion?->precio_base ?? 0;
                $porcentaje = $cita->prestacion?->comision_equipo ?? 0;
                return ($precio * $porcentaje) / 100;
            });
        }
    }


    // Relación con usuario
    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }
}

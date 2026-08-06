<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Modelo BloqueoHorario
 *
 * Representa a la tabla `bloqueos_horario` en la base de datos.
 * Función: Define los rangos de horarios en los que un veterinario o sucursal no está disponible para citas.
 */

class BloqueoHorario extends Model
{
    protected $table = 'bloqueos_horario';

    protected $fillable = [
        'veterinario_id',
        'fecha_inicio',
        'fecha_fin',
        'hora_inicio',
        'especialidad_id',
        'sucursal_id',
        'hora_fin',
        'motivo',
    ];

    protected $casts = [
        'fecha_inicio' => 'date:Y-m-d',
        'fecha_fin' => 'date:Y-m-d',
    ];

    public function veterinario()
    {
        return $this->belongsTo(Veterinario::class, 'veterinario_id');
    }

    public function especialidad()
    {
        return $this->belongsTo(Especialidad::class, 'especialidad_id');
    }

    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class, 'sucursal_id');
    }
}

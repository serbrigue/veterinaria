<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Modelo AplicacionVacuna
 *
 * Representa a la tabla `aplicacion_vacunas` en la base de datos.
 * Función: Registra las aplicaciones de vacunas administradas a las mascotas.
 */


class AplicacionVacuna extends Model
{
    protected $fillable = [
        'cita_id',
        'mascota_id',
        'nombre_vacuna',
        'fecha_aplicacion',
        'fecha_proxima_dosis',
        'numero_lote',
        'notas',
    ];

    public function fichaClinica()
    {
        return $this->belongsTo(FichaClinica::class);
    }

    public function mascota()
    {
        return $this->belongsTo(Mascota::class);
    }
}

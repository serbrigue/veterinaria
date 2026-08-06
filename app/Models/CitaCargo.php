<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Modelo CitaCargo
 *
 * Representa a la tabla `citas_cargo` en la base de datos.
 * Función: Registra los cargos, costos o detalles financieros asociados a una cita específica.
 */
class CitaCargo extends Model
{
    // Definimos el nombre de la tabla
    protected $table = 'citas_cargo';

    // Definimos los campos que se pueden llenar
    protected $fillable = [
        'cita_id',
        'prestacion_id',
        'insumo_id',
        'cantidad',
        'precio_unitario',
        'subtotal',
        'pago_vet',
    ];

    // Relaciones

    // Relación con cita
    public function cita()
    {
        return $this->belongsTo(Cita::class, 'cita_id');
    }

    // Relación con prestación
    public function prestacion()
    {
        return $this->belongsTo(Prestacion::class, 'prestacion_id');
    }

    // Relación con insumo
    public function insumo()
    {
        return $this->belongsTo(Insumo::class, 'insumo_id');
    }
}

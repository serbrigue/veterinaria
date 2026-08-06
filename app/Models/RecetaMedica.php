<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Modelo RecetaMedica
 *
 * Representa a la tabla `receta_medicas` en la base de datos.
 * Función: Almacena las recetas de medicamentos prescritas por el veterinario durante una atención.
 */
class RecetaMedica extends Model
{
    protected $fillable = [
        'ficha_clinica_id',
        'medicamentos',
        'indicaciones_generales',
        'comprado_en_clinica',
    ];

    protected $casts = [
        'medicamentos' => 'array',
        'comprado_en_clinica' => 'boolean',
    ];

    public function fichaClinica()
    {
        return $this->belongsTo(FichaClinica::class);
    }
}

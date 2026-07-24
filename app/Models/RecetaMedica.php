<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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

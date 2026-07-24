<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AplicacionVacuna extends Model
{
    protected $fillable = [
        'ficha_clinica_id',
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

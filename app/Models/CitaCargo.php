<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CitaCargo extends Model
{
    protected $table = 'citas_cargo';

    protected $fillable = [
        'cita_id',
        'prestacion_id',
        'insumo_id',
        'cantidad',
        'precio_unitario',
        'subtotal',
        'pago_vet'
    ];

    public function cita()
    {
        return $this->belongsTo(Cita::class, 'cita_id');
    }

    public function prestacion()
    {
        return $this->belongsTo(Prestacion::class, 'prestacion_id');
    }

    public function insumo()
    {
        return $this->belongsTo(Insumo::class, 'insumo_id');
    }
}

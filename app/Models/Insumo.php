<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Insumo extends Model
{
    protected $fillable = [
        'sucursal_id',
        'nombre',
        'descripcion',
        'precio_venta',
        'stock_actual',
        'stock_minimo',
        'estado',
    ];

    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class);
    }
}

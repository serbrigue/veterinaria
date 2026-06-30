<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Insumo extends Model
{
    use \App\Traits\ClearsCache;

    public $cacheKeys = ['insumos_full'];

    protected $fillable = [
        'sucursal_id',
        'nombre',
        'descripcion',
        'precio_venta',
        'stock_actual',
        'stock_minimo',
        'estado',
        'categoria_insumo_id',
    ];

    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class);
    }

    public function categoriaInsumo()
    {
        return $this->belongsTo(CategoriaInsumo::class, 'categoria_insumo_id');
    }
}

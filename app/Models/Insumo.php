<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Insumo extends Model
{
    # Usamos el trait para limpiar la caché
    use \App\Traits\ClearsCache;

    # Definimos las claves de caché
    public $cacheKeys = ['insumos_full'];

    # Definimos el nombre de la tabla
    protected $table = 'insumos';

    # Definimos los campos que se pueden llenar
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

    # Relaciones

    # Relación con sucursal
    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class);
    }

    # Relación con categoria de insumo
    public function categoriaInsumo()
    {
        return $this->belongsTo(CategoriaInsumo::class, 'categoria_insumo_id');
    }
}

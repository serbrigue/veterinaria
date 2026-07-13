<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Box extends Model
{
    // Trait para limpiar el cache
    use \App\Traits\ClearsCache;

    // Definimos las claves del cache
    public $cacheKeys = ['boxes_full', 'boxes_simple', 'sucursales_full'];

    // Definimos los campos que se pueden llenar
    protected $fillable = [
        'nombre',
        'descripcion',
        'sucursal_id',
        'categoria_prestacion_id',
        'imagen_url',
    ];

    // Definimos las relaciones

    // Relación con sucursal
    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class, 'sucursal_id');
    }

    // Relación con categoría de prestación
    public function categoriaPrestacion()
    {
        return $this->belongsTo(CategoriaPrestacion::class, 'categoria_prestacion_id');
    }
}

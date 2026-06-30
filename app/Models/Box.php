<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Box extends Model
{
    use \App\Traits\ClearsCache;

    public $cacheKeys = ['boxes_full', 'sucursales_full'];
    protected $fillable = [
        'nombre',
        'descripcion',
        'sucursal_id',
        'categoria_prestacion_id',
    ];

    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class, 'sucursal_id');
    }

    public function categoriaPrestacion()
    {
        return $this->belongsTo(CategoriaPrestacion::class, 'categoria_prestacion_id');
    }

}

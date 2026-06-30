<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CategoriaPrestacion extends Model
{
    use \App\Traits\ClearsCache;

    public $cacheKeys = ['categorias_prestaciones_full'];

    protected $table = 'categorias_prestaciones';

    protected $fillable = [
        'nombre',
        'descripcion',
    ];

    /**
     * Un tipo de prestación puede estar asociado a muchas prestaciones.
     */
    public function prestaciones()
    {
        return $this->hasMany(Prestacion::class, 'categoria_prestacion_id');
    }

    /**
     * Un tipo de prestación puede estar asociado a muchos boxes.
     */
    public function boxes()
    {
        return $this->hasMany(Box::class, 'categoria_prestacion_id');
    }
}

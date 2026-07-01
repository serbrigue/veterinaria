<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CategoriaPrestacion extends Model
{
    # Trait para limpiar el cache
    use \App\Traits\ClearsCache;

    # Definimos las claves del cache
    public $cacheKeys = ['categorias_prestaciones_full'];

    # Definimos el nombre de la tabla
    protected $table = 'categorias_prestaciones';

    # Definimos los campos que se pueden llenar
    protected $fillable = [
        'nombre',
        'descripcion',
    ];

    # Relación con prestaciones
    public function prestaciones()
    {
        return $this->hasMany(Prestacion::class, 'categoria_prestacion_id');
    }

    # Relación con boxes
    public function boxes()
    {
        return $this->hasMany(Box::class, 'categoria_prestacion_id');
    }
}

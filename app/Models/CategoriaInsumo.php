<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CategoriaInsumo extends Model
{
    # Trait para limpiar el cache
    use \App\Traits\ClearsCache;

    # Definimos las claves del cache
    public $cacheKeys = ['categorias_insumos_full'];

    # Definimos el nombre de la tabla
    protected $table = 'categorias_insumos';

    # Definimos los campos que se pueden llenar
    protected $fillable = [
        'nombre',
        'descripcion',
    ];

    # Relación con insumos
    public function insumos()
    {
        return $this->hasMany(Insumo::class, 'categoria_insumo_id');
    }
}

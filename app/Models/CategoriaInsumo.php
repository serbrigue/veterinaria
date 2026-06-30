<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CategoriaInsumo extends Model
{
    use \App\Traits\ClearsCache;

    public $cacheKeys = ['categorias_insumos_full'];

    protected $table = 'categorias_insumos';

    protected $fillable = [
        'nombre',
        'descripcion',
    ];

    /**
     * Una categoría de insumo agrupa muchos insumos.
     */
    public function insumos()
    {
        return $this->hasMany(Insumo::class, 'categoria_insumo_id');
    }
}

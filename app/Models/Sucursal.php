<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sucursal extends Model
{

    # Trait para limpiar la caché
    use \App\Traits\ClearsCache;

    # Definimos las claves de caché
    public $cacheKeys = ['sucursales_full', 'sucursales_simple'];

    # Definimos el nombre de la tabla
    protected $table = 'sucursales';

    # Campos que se pueden llenar
    protected $fillable = [
        'nombre',
        'direccion',
        'telefono',

    ];

    #Relaciones

    #Una sucursal tiene muchas cajas
    public function boxes()
    {
        return $this->hasMany(Box::class, 'sucursal_id');
    }

    #Una sucursal tiene muchos veterinarios
    public function veterinarios()
    {
        return $this->hasMany(Veterinario::class, 'sucursal_id');
    }

    #Una sucursal tiene muchas citas a traves de las cajas
    public function citas()
    {
        return $this->hasManyThrough(Cita::class, Box::class);
    }
}

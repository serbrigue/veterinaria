<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sucursal extends Model
{
    use \App\Traits\ClearsCache;

    public $cacheKeys = ['sucursales_full', 'sucursales_simple'];
    protected $table = 'sucursales';
    protected $fillable = [
        'nombre',
        'direccion',
        'telefono',

    ];


    public function boxes()
    {
        return $this->hasMany(Box::class, 'sucursal_id');
    }

    public function veterinarios()
    {
        return $this->hasMany(Veterinario::class, 'sucursal_id');
    }

    public function citas(){
        return $this->hasManyThrough(Cita::class, Box::class);
    }

}

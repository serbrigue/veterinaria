<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Prestacion extends Model
{
    use \App\Traits\ClearsCache;

    public $cacheKeys = ['prestaciones_full'];
    protected $table = 'prestaciones';

    protected $fillable = [
        'sucursal_id',
        'nombre',
        'descripcion',
        'especialidad_id',
        'precio_base',
        'comision_vet',

    ];

    public function especialidad()
    {
        return $this->belongsTo(Especialidad::class, 'especialidad_id');
    }

    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class);
    }
}

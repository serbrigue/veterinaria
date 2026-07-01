<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Prestacion extends Model
{
    # Trait para limpiar la caché
    use \App\Traits\ClearsCache;

    # Definimos las claves de caché
    public $cacheKeys = ['prestaciones_full'];

    # Definimos el nombre de la tabla
    protected $table = 'prestaciones';

    # Campos que se pueden llenar
    protected $fillable = [
        'sucursal_id',
        'nombre',
        'descripcion',
        'especialidad_id',
        'precio_base',
        'comision_vet',
        'comision_equipo',
        'categoria_prestacion_id',
    ];

    # Relaciones

    # Relación con especialidad
    public function especialidad()
    {
        return $this->belongsTo(Especialidad::class, 'especialidad_id');
    }

    # Relación con sucursal
    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class);
    }

    # Relación con categoria de prestacion
    public function categoriaPrestacion()
    {
        return $this->belongsTo(CategoriaPrestacion::class, 'categoria_prestacion_id');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Especie extends Model
{
    # Usamos el trait para limpiar la caché
    use \App\Traits\ClearsCache;

    # Definimos las claves de caché
    public $cacheKeys = ['especies_simple', 'razas_full'];

    # Definimos el nombre de la tabla
    protected $table = 'especies';

    # Definimos los campos que se pueden llenar
    protected $fillable = [
        'nombre',
        'descripcion',
        'imagen_url',
        'creado_por',
    ];

    # Relaciones

    # Relación con usuario
    public function creado_por()
    {
        return $this->belongsTo(User::class, 'creado_por');
    }

    # Relación con razas
    public function razas()
    {
        return $this->hasMany(Raza::class, 'especie_id');
    }
}

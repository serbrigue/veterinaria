<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Raza extends Model
{
    # Trait para limpiar la caché
    use \App\Traits\ClearsCache;

    # Definimos las claves de caché
    public $cacheKeys = ['razas_full'];

    # Definimos el nombre de la tabla
    protected $table = 'razas';

    # Campos que se pueden llenar
    protected $fillable = [
        'nombre',
        'descripcion',
        'especie_id',
        'creado_por',
        'imagen_url'
    ];

    # Relaciones

    # Relación con especie
    public function especie()
    {
        return $this->belongsTo(Especie::class, 'especie_id');
    }

    # Relación con usuario
    public function creado_por()
    {
        return $this->belongsTo(User::class, 'creado_por');
    }
}

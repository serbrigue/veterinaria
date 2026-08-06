<?php

namespace App\Models;

use App\Traits\ClearsCache;
use App\Traits\HasStorageAttributes;
use Illuminate\Database\Eloquent\Model;

/**
 * Modelo Especie
 *
 * Representa a la tabla `especies` en la base de datos.
 * Función: Catálogo de las diferentes especies de animales (Perro, Gato, Exótico) que se atienden.
 */
class Especie extends Model
{
    // Usamos el trait para limpiar la caché
    use ClearsCache;
    use HasStorageAttributes;

    // Definimos las claves de caché
    public $cacheKeys = ['especies_simple', 'razas_full'];

    // Definimos el nombre de la tabla
    protected $table = 'especies';

    // Definimos los campos que se pueden llenar
    protected $fillable = [
        'nombre',
        'descripcion',
        'imagen_url',
        'creado_por',
    ];

    // Relaciones

    // Relación con usuario
    public function creado_por()
    {
        return $this->belongsTo(User::class, 'creado_por');
    }

    // Relación con razas
    public function razas()
    {
        return $this->hasMany(Raza::class, 'especie_id');
    }
}

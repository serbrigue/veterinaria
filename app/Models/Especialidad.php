<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Especialidad extends Model
{
    // Usamos el trait para limpiar la caché
    use \App\Traits\ClearsCache, HasFactory;

    // Definimos las claves de caché
    public $cacheKeys = ['especialidades_full', 'veterinarios_full', 'prestaciones_full'];

    // Definimos el nombre de la tabla
    protected $table = 'especialidades';

    // Definimos los campos que se pueden llenar
    protected $fillable = [
        'nombre',
        'descripcion',
    ];

    // Relación con veterinarios
    public function veterinarios()
    {
        return $this->hasMany(Veterinario::class, 'especialidad_id');
    }
}

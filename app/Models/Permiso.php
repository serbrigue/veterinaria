<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Modelo Permiso
 *
 * Representa a la tabla `permisos` en la base de datos.
 * Función: Define los diferentes permisos específicos del sistema para el control de acceso y autorización.
 */
class Permiso extends Model
{
    // Atributos
    protected $table = 'permisos';

    // Campos que se pueden llenar
    protected $fillable = [
        'nombre',
        'descripcion',
    ];

    // Relaciones

    // Relación muchos a muchos con los roles
    public function roles()
    {
        return $this->belongsToMany(Rol::class, 'permiso_rol', 'permiso_id', 'rol_id');
    }
}

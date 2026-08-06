<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Modelo Rol
 *
 * Representa a la tabla `roles` en la base de datos.
 * Función: Define los roles de usuario del sistema (ej. admin, veterinario, cliente, secretaria) para agrupar permisos.
 */
class Rol extends Model
{
    // Definimos el nombre de la tabla
    protected $table = 'roles';

    // Campos que se pueden llenar
    protected $fillable = [
        'nombre_interno',
        'nombre_legible',
    ];

    // Relaciones

    // Relación uno a muchos con los usuarios
    public function usuarios()
    {
        return $this->hasMany(User::class, 'rol_id');
    }

    // Relación muchos a muchos con los permisos
    public function permisos()
    {
        return $this->belongsToMany(Permiso::class, 'permiso_rol', 'rol_id', 'permiso_id');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Rol extends Model
{

    protected $table = 'roles';

    protected $fillable = [
        'nombre_interno',
        'nombre_legible',
    ];

    /**
     * Relación uno a muchos con los usuarios.
     * Un rol pertenece a muchos usuarios.
     */
    public function usuarios()
    {
        return $this->hasMany(User::class, 'rol_id');
    }

    /**
     * Relación muchos a muchos con los permisos.
     * Un rol tiene muchos permisos asociados.
     */
    public function permisos()
    {
        return $this->belongsToMany(Permiso::class, 'permiso_rol', 'rol_id', 'permiso_id');
    }
}

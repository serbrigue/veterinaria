<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Permiso extends Model
{
    # Atributos
    protected $table = 'permisos';

    # Campos que se pueden llenar
    protected $fillable = [
        'nombre',
        'descripcion',
    ];

    # Relaciones

    # Relación muchos a muchos con los roles
    public function roles()
    {
        return $this->belongsToMany(Rol::class, 'permiso_rol', 'permiso_id', 'rol_id');
    }
}

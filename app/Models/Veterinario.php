<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Veterinario extends Model
{
    #Traits para usar ClearsCache
    #ClearsCache para limpiar la caché
    use \App\Traits\ClearsCache;

    #Definimos las claves de caché
    public $cacheKeys = ['veterinarios_full', 'veterinarios_simple', 'sucursales_full'];

    #Definimos el nombre de la tabla
    protected $table = 'veterinarios';

    #Campos que se pueden llenar
    protected $fillable = [
        'telefono',
        'direccion',
        'foto_perfil_url',
        'user_id',
        'sucursal_id',
        'especialidad_id',
    ];

    #Append para que se muestre el nombre del usuario
    protected $appends = ['nombre'];

    #Accessor virtual 'nombre'.
    #Extrae el nombre del usuario asociado para facilitar el renderizado en frontend.
    public function getNombreAttribute()
    {
        return $this->usuario?->name;
    }

    #Accessor virtual 'foto_perfil_url'.
    #Extrae la foto de perfil del usuario asociado para facilitar el renderizado en frontend.
    public function foto_perfil_url()
    {
        return $this->foto_perfil_url;
    }

    #Relaciones

    #Un veterinario pertenece a un usuario
    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    #Un veterinario pertenece a una sucursal
    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class, 'sucursal_id');
    }

    #Un veterinario pertenece a una especialidad
    public function especialidad()
    {
        return $this->belongsTo(Especialidad::class, 'especialidad_id');
    }

    #Un veterinario tiene muchas citas
    public function citas()
    {
        return $this->hasMany(Cita::class, 'veterinario_id');
    }
}

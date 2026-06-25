<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Veterinario extends Model
{
    use \App\Traits\ClearsCache;

    public $cacheKeys = ['veterinarios_full', 'veterinarios_simple', 'sucursales_full'];
    protected $fillable = [
        'telefono',
        'direccion',
        'foto_perfil_url',
        'user_id',
        'sucursal_id',
        'especialidad_id',
    ];

    protected $appends = ['nombre'];

    /**
     * Accessor virtual 'nombre'.
     * Extrae el nombre del usuario asociado para facilitar el renderizado en frontend.
     */
    public function getNombreAttribute()
    {
        return $this->usuario?->name;
    }

    public function foto_perfil_url()
    {
        return $this->foto_perfil_url;
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class, 'sucursal_id');
    }
    public function especialidad()
    {
        return $this->belongsTo(Especialidad::class, 'especialidad_id');
    }

    public function citas()
    {
        return $this->hasMany(Cita::class, 'veterinario_id');
    }
}

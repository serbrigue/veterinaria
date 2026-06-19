<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Veterinario extends Model
{
    protected $fillable = [
        'telefono',
        'direccion',
        'foto_perfil_url',
        'user_id',
        'sucursal_id',
        'especialidad_id',
    ];

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
}


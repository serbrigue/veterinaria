<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Especie extends Model
{
    protected $fillable = [
        'nombre',
        'descripcion',
        'imagen_url',
        'creado_por',
        
    ];

    public function creado_por()
    {
        return $this->belongsTo(User::class, 'creado_por');
    }

    public function razas()
    {
        return $this->hasMany(Raza::class, 'especie_id');
    }
}

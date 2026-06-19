<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Especie extends Model
{
    protected $fillable = [
        'nombre',
        'descripcion',
        'user_id',
    ];

    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // MÓDULO 3: agregar razas() hasMany(Raza::class, 'especie_id')
}

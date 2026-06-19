<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Raza extends Model
{
    protected $fillable = [
        'nombre',
        'descripcion',
        'especie_id',
        'creado_por',
    ];

    public function especie()
    {
        return $this->belongsTo(Especie::class, 'especie_id');
    }

    public function creado_por(){
        return $this->belongsTo(User::class, 'creado_por');
    }
}

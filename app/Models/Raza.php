<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Raza extends Model
{
    protected $fillable = [
        'nombre',
        'descripcion',
        'especie_id',
        'user_id',
    ];

    public function especie()
    {
        return $this->belongsTo(Especie::class, 'especie_id');
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

}

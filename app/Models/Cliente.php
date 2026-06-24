<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cliente extends Model
{
    protected $fillable = [
        'telefono',
        'direccion',
        'foto_perfil_url',
        'user_id',
    ];

    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function mascotas()
    {
        return $this->hasMany(Mascota::class, 'cliente_id');
    }

    public function transacciones()
    {
        return $this->hasMany(Transaccion::class);
    }


    
}

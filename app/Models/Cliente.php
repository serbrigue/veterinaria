<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cliente extends Model
{
    # Definimos los campos que se pueden llenar
    protected $fillable = [
        'telefono',
        'direccion',
        'foto_perfil_url',
        'user_id',
    ];

    # Relaciones

    # Relación con usuario
    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    # Relación con mascotas
    public function mascotas()
    {
        return $this->hasMany(Mascota::class, 'cliente_id');
    }

    # Relación con transacciones
    public function transacciones()
    {
        return $this->hasMany(Transaccion::class);
    }
}

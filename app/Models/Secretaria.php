<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Modelo Secretaria
 *
 * Representa a la tabla `secretarias` en la base de datos.
 * Función: Representa al personal administrativo encargado de gestionar citas y clientes en una sucursal.
 */
class Secretaria extends Model
{
    protected $table = 'secretarias';

    protected $fillable = [
        'user_id',
        'sucursal_id',
        'telefono',
    ];

    // Una secretaria pertenece a un usuario
    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Una secretaria pertenece a una sucursal
    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class, 'sucursal_id');
    }
}

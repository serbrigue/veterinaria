<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EquipoMedico extends Model
{
    protected $table = 'equipos_medicos';

    protected $fillable = [
        'cita_id',
        'usuario_id',
        'rol_id',
    ];

    /**
     * La cita a la que pertenece este miembro del equipo.
     */
    public function cita()
    {
        return $this->belongsTo(Cita::class, 'cita_id');
    }

    /**
     * El usuario (anestesista, arsenalero, TENS, etc.) asignado.
     */
    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    /**
     * El rol médico que cumple este usuario en la cita.
     */
    public function rol()
    {
        return $this->belongsTo(Rol::class, 'rol_id');
    }
}

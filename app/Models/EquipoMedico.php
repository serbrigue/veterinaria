<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EquipoMedico extends Model
{
    # Definimos el nombre de la tabla
    protected $table = 'equipos_medicos';

    # Definimos los campos que se pueden llenar
    protected $fillable = [
        'cita_id',
        'usuario_id',
        'rol_id',
    ];

    # Relaciones

    # Relación con cita
    public function cita()
    {
        return $this->belongsTo(Cita::class, 'cita_id');
    }

    # Relación con usuario
    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    # Relación con rol
    public function rol()
    {
        return $this->belongsTo(Rol::class, 'rol_id');
    }
}

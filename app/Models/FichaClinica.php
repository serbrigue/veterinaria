<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FichaClinica extends Model
{
    protected $fillable = [
        'cita_id',
        'mascota_id',
        'veterinario_id',
        'peso_actual',
        'frecuencia_cardiaca',
        'temperatura',
        'anamnesis',
        'sintomas',
        'diagnostico',
    ];

    public function cita()
    {
        return $this->belongsTo(Cita::class);
    }

    public function mascota()
    {
        return $this->belongsTo(Mascota::class);
    }

    public function veterinario()
    {
        return $this->belongsTo(Veterinario::class);
    }

    public function recetas()
    {
        return $this->hasMany(RecetaMedica::class);
    }

    public function vacunas()
    {
        return $this->hasMany(AplicacionVacuna::class, 'cita_id', 'cita_id');
    }
}

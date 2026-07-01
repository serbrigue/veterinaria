<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class Mascota extends Model
{
    # Atributos adicionales
    protected $appends = [
        'fecha_nacimiento_formato',
        'edad_texto',
    ];

    # Campos que se pueden llenar
    protected $fillable = [
        'nombre',
        'descripcion',
        'sexo',
        'fecha_nacimiento',
        'peso_kg',
        'color',
        'esterilizado',
        'cliente_id',
        'imagen_url',
        'raza_id',
    ];

    protected $casts = [
        'fecha_nacimiento' => 'date',
        'peso_kg' => 'decimal:2',
        'esterilizado' => 'boolean',
    ];

    # Relaciones

    # Relación con cliente
    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }

    # Relación con raza
    public function raza()
    {
        return $this->belongsTo(Raza::class, 'raza_id');
    }

    # Función para obtener la foto de la mascota
    public function foto_url()
    {
        return $this->imagen_url;
    }

    # Relación con citas
    public function citas()
    {
        return $this->hasMany(Cita::class, 'mascota_id');
    }

    # Getters

    # Obtener la fecha de nacimiento en formato d/m/Y
    public function getFechaNacimientoFormatoAttribute(): ?string
    {
        if (! $this->fecha_nacimiento) {
            return null;
        }

        return Carbon::parse($this->fecha_nacimiento)->format('d/m/Y');
    }

    # Obtener la edad en texto
    public function getEdadTextoAttribute(): ?string
    {
        if (! $this->fecha_nacimiento) {
            return null;
        }

        return Carbon::parse($this->fecha_nacimiento)->age . ' años';
    }
}

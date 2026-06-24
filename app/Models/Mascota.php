<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class Mascota extends Model
{
    protected $appends = [
        'fecha_nacimiento_formato',
        'edad_texto',
    ];
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

    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }

    public function raza()
    {
        return $this->belongsTo(Raza::class, 'raza_id');
    }

    public function foto_url()
    {
        return $this->imagen_url;
    }

    public function citas()
    {
        return $this->hasMany(Cita::class, 'mascota_id');
    }

    public function getFechaNacimientoFormatoAttribute(): ?string
    {
        if (! $this->fecha_nacimiento) {
            return null;
        }

        return Carbon::parse($this->fecha_nacimiento)->format('d/m/Y');
    }

    public function getEdadTextoAttribute(): ?string
    {
        if (! $this->fecha_nacimiento) {
            return null;
        }

        return Carbon::parse($this->fecha_nacimiento)->age . ' años';
    }
}

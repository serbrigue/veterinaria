<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Veterinario;
use App\Models\Box;
use App\Models\Mascota;
use App\Models\Cliente;

class Cita extends Model

{
    protected $fillable = [
        'titulo',
        'descripcion',
        'fecha_hora',
        'hora_termino',
        'estado',
        'tipo',
        'notas',
        'veterinario_id',
        'box_id',
        'mascota_id',
        'prestacion_id',
    ];

    # Atributos adicionales
    protected $appends = ['cliente'];

    # Relaciones

    # Relación con veterinario
    public function veterinario()
    {
        return $this->belongsTo(Veterinario::class, 'veterinario_id');
    }

    # Relación con prestación
    public function prestacion()
    {
        return $this->belongsTo(Prestacion::class, 'prestacion_id');
    }

    # Relación con box
    public function box()
    {
        return $this->belongsTo(Box::class, 'box_id');
    }

    # Relación con mascota
    public function mascota()
    {
        return $this->belongsTo(Mascota::class, 'mascota_id');
    }

    # Relación con transacción
    public function transaccion()
    {
        return $this->hasOne(Transaccion::class);
    }

    # Relación con cargos
    public function cargos()
    {
        return $this->hasMany(CitaCargo::class, 'cita_id');
    }

    # Relación con equipo médico
    public function equipoMedico()
    {
        return $this->hasMany(EquipoMedico::class, 'cita_id');
    }

    # Método que devuelve el cliente de la cita
    public function getClienteAttribute()
    {
        $cliente = $this->mascota?->cliente;
        if (!$cliente) {
            return null;
        }
        return (object)[
            'id' => $cliente->id,
            'nombre' => $cliente->usuario?->name,
            'email' => $cliente->usuario?->email,
        ];
    }
}

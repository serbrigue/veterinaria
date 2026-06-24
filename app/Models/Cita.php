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

    protected $appends = ['cliente'];

    public function veterinario()
    {
        return $this->belongsTo(Veterinario::class, 'veterinario_id');
    }

    public function prestacion()
    {
        return $this->belongsTo(Prestacion::class, 'prestacion_id');
    }

    public function box()
    {
        return $this->belongsTo(Box::class, 'box_id');
    }

    public function mascota()
    {
        return $this->belongsTo(Mascota::class, 'mascota_id');
    }

    public function transaccion()
    {
        return $this->hasOne(Transaccion::class);
    }

    /**
     * Accessor virtual 'cliente'.
     * Extrae y mapea la información del propietario de la mascota
     * para que sea devuelta directamente en la serialización JSON de la cita.
     */
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

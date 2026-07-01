<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PagoVeterinario extends Model
{
    # Atributos
    protected $fillable = [
        'veterinario_id',
        'usuario_id',
        'mes',
        'anio',
        'monto_total',
        'estado',
    ];

    # Relaciones

    # Relación con veterinario
    public function veterinario()
    {
        return $this->belongsTo(Veterinario::class);
    }

    # Relación con usuario
    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }
}

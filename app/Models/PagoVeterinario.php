<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PagoVeterinario extends Model
{
    protected $fillable = [
        'veterinario_id',
        'mes',
        'anio',
        'monto_total',
        'estado',
    ];

    public function veterinario()
    {
        return $this->belongsTo(Veterinario::class);
    }
}

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
        'notas',
        'veterinario_id',
        'box_id',
        'mascota_id',
    ];

    public function veterinario()
    {
        return $this->belongsTo(Veterinario::class, 'veterinario_id');
    }

    public function box()
    {
        return $this->belongsTo(Box::class, 'box_id');
    }

    public function mascota()
    {
        return $this->belongsTo(Mascota::class, 'mascota_id');
    }

    public function cliente()
    {
        return $this->mascota ? $this->mascota->cliente : null;
    }
}

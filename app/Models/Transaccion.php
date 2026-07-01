<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaccion extends Model
{

    #Tabla
    protected $table = 'transacciones';

    #Campos que se pueden llenar
    protected $fillable = [
        'cita_id',
        'cliente_id',
        'monto_total',
        'monto_pagado',
        'estado',
        'metodo_pago',
        'fecha_pago',
    ];

    #Casteos
    protected $casts = [
        'monto_total' => 'decimal:2',
        'monto_pagado' => 'decimal:2',
        'fecha_pago' => 'datetime',
    ];

    #Relaciones

    #Una transaccion pertenece a una cita
    public function cita()
    {
        return $this->belongsTo(Cita::class);
    }

    #Un cliente tiene muchas transacciones
    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Modelo MovimientoInventario
 *
 * Representa a la tabla `movimiento_inventarios` en la base de datos.
 * Función: Registra las entradas y salidas de stock de los diferentes insumos para llevar el control de inventario.
 */
class MovimientoInventario extends Model
{
    use HasFactory;

    protected $table = 'movimiento_inventarios';

    protected $fillable = [
        'insumo_id',
        'tipo',
        'cantidad',
        'motivo',
        'usuario_id',
        'cita_id',
    ];

    // Relaciones
    public function insumo()
    {
        return $this->belongsTo(Insumo::class);
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function cita()
    {
        return $this->belongsTo(Cita::class);
    }
}

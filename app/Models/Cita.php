<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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

    // Atributos adicionales
    protected $appends = ['cliente'];

    // Relaciones

    // Relación con veterinario
    public function veterinario()
    {
        return $this->belongsTo(Veterinario::class, 'veterinario_id');
    }

    // Relación con prestación
    public function prestacion()
    {
        return $this->belongsTo(Prestacion::class, 'prestacion_id');
    }

    // Relación con box
    public function box()
    {
        return $this->belongsTo(Box::class, 'box_id');
    }

    // Relación con mascota
    public function mascota()
    {
        return $this->belongsTo(Mascota::class, 'mascota_id');
    }

    // Relación con transacción
    public function transaccion()
    {
        return $this->hasOne(Transaccion::class);
    }

    // Relación con cargos
    public function cargos()
    {
        return $this->hasMany(CitaCargo::class, 'cita_id');
    }

    // Relación con equipo médico
    public function equipoMedico()
    {
        return $this->hasMany(EquipoMedico::class, 'cita_id');
    }

    // Relación con ficha clínica
    public function fichaClinica()
    {
        return $this->hasOne(FichaClinica::class, 'cita_id');
    }

    // Método que devuelve el cliente de la cita
    public function getClienteAttribute()
    {
        $cliente = $this->mascota?->cliente;
        if (! $cliente) {
            return null;
        }

        return (object) [
            'id' => $cliente->id,
            'nombre' => $cliente->usuario?->name,
            'email' => $cliente->usuario?->email,
        ];
    }

    /**
     * Calcula las alertas visibles solo para secretarias.
     * Retorna un array de objetos {tipo, mensaje, icono}.
     */
    public function getAlertasSecretariaAttribute(): array
    {
        $alertas = [];

        if ($this->necesitaBoxAsignado()) {
            $alertas[] = [
                'tipo' => 'sin_box',
                'mensaje' => 'Sin box asignado',
                'icono' => 'bi-door-closed',
            ];
        }

        if ($this->faltaEquipoQuirofano()) {
            $alertas[] = [
                'tipo' => 'sin_equipo',
                'mensaje' => 'Falta equipo médico para quirófano',
                'icono' => 'bi-people',
            ];
        }

        return $alertas;
    }

    private function necesitaBoxAsignado(): bool
    {
        return is_null($this->box_id)
            && in_array($this->estado, ['pendiente', 'en_curso']);
    }

    private function faltaEquipoQuirofano(): bool
    {
        $esCirugia = $this->prestacion
            ?->categoriaPrestacion
            ?->nombre === 'Cirugia';

        if (! $esCirugia) {
            return false;
        }

        if (in_array($this->estado, ['completada', 'cancelada'])) {
            return false;
        }

        $equipo = $this->relationLoaded('equipoMedico')
            ? $this->equipoMedico
            : $this->equipoMedico()->with('rol')->get();

        return ! $equipo->contains(
            fn ($miembro) => $miembro->rol?->nombre_interno === 'arsenalero'
        );
    }
}

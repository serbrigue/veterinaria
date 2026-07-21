<?php

namespace App\Exports;

use App\Models\Cita;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class CitasExport implements FromQuery, WithHeadings, WithMapping
{
    public function query()
    {
        return Cita::query()->with([
            'veterinario.usuario',
            'mascota.cliente.usuario',
            'prestacion',
            'box.sucursal',
            'transaccion',
            'cargos.prestacion',
            'cargos.insumo',
        ]);
    }

    public function headings(): array
    {
        return [
            'Titulo',
            'Descripcion',
            'Fecha Hora',
            'Hora Termino',
            'Estado',
            'Tipo',
            'Notas',
            'Veterinario',
            'Mascota',
            'Cliente Email',
            'Prestacion',
            'Box',
            'Sucursal',
            'Valor',
            'Estado Transaccion',
            'Cargos',
        ];
    }

    public function map($cita): array
    {
        return [
            $cita->titulo,
            $cita->descripcion,
            $cita->fecha_hora,
            $cita->hora_termino,
            $cita->estado,
            $cita->tipo,
            $cita->notas,
            $cita->veterinario?->usuario?->name,
            $cita->mascota?->nombre,
            $cita->mascota?->cliente?->usuario?->email,
            $cita->prestacion?->nombre,
            $cita->box?->nombre,
            $cita->box?->sucursal?->nombre,
            $cita->transaccion ? $cita->transaccion->monto_total : null,
            $cita->transaccion ? $cita->transaccion->estado : null,
            $cita->cargos->map(function ($cargo) {
                if ($cargo->prestacion) return $cargo->prestacion->nombre;
                if ($cargo->insumo) return $cargo->insumo->nombre;
                return 'Cargo General';
            })->implode(', '),
        ];
    }
}

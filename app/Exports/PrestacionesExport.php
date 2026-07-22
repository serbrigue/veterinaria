<?php

namespace App\Exports;

use App\Models\Prestacion;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class PrestacionesExport implements FromQuery, WithHeadings, WithMapping
{
    public function query()
    {
        return Prestacion::query()->with(['sucursal', 'especialidad', 'categoriaPrestacion']);
    }

    public function headings(): array
    {
        return [
            'Nombre',
            'Descripcion',
            'Precio Base',
            'Comision Veterinario',
            'Comision Equipo',
            'Sucursal',
            'Especialidad',
            'Categoria',
        ];
    }

    public function map($prestacion): array
    {
        return [
            $prestacion->nombre,
            $prestacion->descripcion,
            $prestacion->precio_base,
            $prestacion->comision_vet,
            $prestacion->comision_equipo,
            $prestacion->sucursal?->nombre,
            $prestacion->especialidad?->nombre,
            $prestacion->categoriaPrestacion?->nombre,
        ];
    }
}

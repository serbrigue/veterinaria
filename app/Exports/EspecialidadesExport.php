<?php

namespace App\Exports;

use App\Models\Especialidad;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class EspecialidadesExport implements FromQuery, WithHeadings, WithMapping
{
    public function query()
    {
        return Especialidad::query();
    }

    public function headings(): array
    {
        return ['Nombre', 'Descripcion'];
    }

    public function map($especialidad): array
    {
        return [
            $especialidad->nombre,
            $especialidad->descripcion,
        ];
    }
}

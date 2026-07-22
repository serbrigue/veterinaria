<?php

namespace App\Exports;

use App\Models\Especie;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class EspeciesExport implements FromQuery, WithHeadings, WithMapping
{
    public function query()
    {
        return Especie::query();
    }

    public function headings(): array
    {
        return ['Nombre', 'Descripcion'];
    }

    public function map($especie): array
    {
        return [
            $especie->nombre,
            $especie->descripcion,
        ];
    }
}

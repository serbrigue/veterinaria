<?php

namespace App\Exports;

use App\Models\CategoriaPrestacion;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class CategoriasPrestacionExport implements FromQuery, WithHeadings, WithMapping
{
    public function query()
    {
        return CategoriaPrestacion::query();
    }

    public function headings(): array
    {
        return ['Nombre', 'Descripcion'];
    }

    public function map($categoria): array
    {
        return [
            $categoria->nombre,
            $categoria->descripcion,
        ];
    }
}

<?php

namespace App\Exports;

use App\Models\Box;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class BoxesExport implements FromQuery, WithHeadings, WithMapping
{
    public function query()
    {
        return Box::query()->with(['sucursal', 'categoriaPrestacion']);
    }

    public function headings(): array
    {
        return ['Nombre', 'Descripcion', 'Sucursal', 'Categoria Prestacion'];
    }

    public function map($box): array
    {
        return [
            $box->nombre,
            $box->descripcion,
            $box->sucursal?->nombre,
            $box->categoriaPrestacion?->nombre,
        ];
    }
}

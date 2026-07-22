<?php

namespace App\Exports;

use App\Models\Sucursal;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class SucursalesExport implements FromQuery, WithHeadings, WithMapping
{
    public function query()
    {
        return Sucursal::query();
    }

    public function headings(): array
    {
        return ['Nombre', 'Direccion', 'Telefono'];
    }

    public function map($sucursal): array
    {
        return [
            $sucursal->nombre,
            $sucursal->direccion,
            $sucursal->telefono,
        ];
    }
}

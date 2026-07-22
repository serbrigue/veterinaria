<?php

namespace App\Imports;

use App\Models\Sucursal;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class SucursalesImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        return Sucursal::firstOrCreate(
            ['nombre' => trim($row['nombre'])],
            [
                'direccion' => $row['direccion'] ?? null,
                'telefono' => $row['telefono'] ?? null,
            ]
        );
    }
}

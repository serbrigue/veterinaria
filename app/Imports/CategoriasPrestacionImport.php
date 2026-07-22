<?php

namespace App\Imports;

use App\Models\CategoriaPrestacion;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class CategoriasPrestacionImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        return CategoriaPrestacion::firstOrCreate(
            ['nombre' => trim($row['nombre'])],
            ['descripcion' => $row['descripcion'] ?? null]
        );
    }
}

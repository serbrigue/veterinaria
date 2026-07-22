<?php

namespace App\Imports;

use App\Models\CategoriaInsumo;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class CategoriasInsumoImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        return CategoriaInsumo::firstOrCreate(
            ['nombre' => trim($row['nombre'])],
            ['descripcion' => $row['descripcion'] ?? null]
        );
    }
}

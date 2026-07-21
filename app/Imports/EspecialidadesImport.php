<?php

namespace App\Imports;

use App\Models\Especialidad;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class EspecialidadesImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        return Especialidad::firstOrCreate(
            ['nombre' => trim($row['nombre'])],
            ['descripcion' => $row['descripcion'] ?? null]
        );
    }
}

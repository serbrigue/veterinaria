<?php

namespace App\Imports;

use App\Models\Especie;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class EspeciesImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        $nombre = trim($row['nombre'] ?? '');

        if (! $nombre || mb_strtolower($nombre) === 'no especificada') {
            return null;
        }

        return Especie::firstOrCreate(
            ['nombre' => $nombre],
            ['descripcion' => $row['descripcion'] ?? null]
        );
    }
}

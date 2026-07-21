<?php

namespace App\Imports;

use App\Models\Especie;
use App\Models\Raza;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class RazasImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        $nombre = trim($row['nombre'] ?? '');

        if (! $nombre || mb_strtolower($nombre) === 'no especificada') {
            return null;
        }

        $especie = Especie::where('nombre', trim($row['especie']))->first();

        if (! $especie) {
            return null;
        }

        return Raza::firstOrCreate(
            ['nombre' => $nombre, 'especie_id' => $especie->id],
            ['descripcion' => $row['descripcion'] ?? null]
        );
    }
}

<?php

namespace App\Imports;

use App\Models\Box;
use App\Models\CategoriaPrestacion;
use App\Models\Sucursal;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class BoxesImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        $sucursal = Sucursal::where('nombre', trim($row['sucursal']))->first();

        if (!$sucursal) {
            return null;
        }

        $categoriaId = null;
        if (!empty($row['categoria_prestacion'])) {
            $categoria = CategoriaPrestacion::where('nombre', trim($row['categoria_prestacion']))->first();
            $categoriaId = $categoria?->id;
        }

        return Box::firstOrCreate(
            ['nombre' => trim($row['nombre']), 'sucursal_id' => $sucursal->id],
            [
                'descripcion' => $row['descripcion'] ?? null,
                'categoria_prestacion_id' => $categoriaId,
            ]
        );
    }
}

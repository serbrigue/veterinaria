<?php

namespace App\Imports;

use App\Models\Prestacion;
use App\Models\Especialidad;
use App\Models\CategoriaPrestacion;
use App\Models\Sucursal;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class PrestacionesImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        $especialidad = Especialidad::where('nombre', 'like', "%{$row['especialidad']}%")->first();
        $categoria = CategoriaPrestacion::where('nombre', 'like', "%{$row['categoria']}%")->first();
        $sucursal = Sucursal::where('nombre', 'like', "%{$row['sucursal']}%")->first();

        return Prestacion::updateOrCreate(
            [
                'nombre' => $row['nombre'] ?? 'Prestación Importada',
                'sucursal_id' => $sucursal ? $sucursal->id : null,
            ],
            [
                'descripcion' => $row['descripcion'] ?? null,
                'precio_base' => floatval($row['precio_base'] ?? 0),
                'comision_vet' => floatval($row['comision_vet'] ?? 0),
                'especialidad_id' => $especialidad ? $especialidad->id : null,
                'categoria_prestacion_id' => $categoria ? $categoria->id : null,
            ]
        );
    }
}

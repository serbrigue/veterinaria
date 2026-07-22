<?php

namespace App\Imports;

use App\Models\Insumo;
use App\Models\CategoriaInsumo;
use App\Models\Sucursal;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class InsumosImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        $categoria = CategoriaInsumo::where('nombre', 'like', "%{$row['categoria']}%")->first();
        $sucursal = Sucursal::where('nombre', 'like', "%{$row['sucursal']}%")->first();

        return Insumo::updateOrCreate(
            [
                'nombre' => $row['nombre'] ?? 'Insumo Importado',
                'sucursal_id' => $sucursal ? $sucursal->id : null,
            ],
            [
                'descripcion' => $row['descripcion'] ?? null,
                'precio_venta' => floatval($row['precio_venta'] ?? 0),
                'stock_actual' => intval($row['stock_actual'] ?? 0),
                'stock_minimo' => intval($row['stock_minimo'] ?? 0),
                'estado' => $row['estado'] ?? 'activo',
                'categoria_insumo_id' => $categoria ? $categoria->id : null,
            ]
        );
    }
}

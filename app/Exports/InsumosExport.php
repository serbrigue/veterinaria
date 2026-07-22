<?php

namespace App\Exports;

use App\Models\Insumo;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class InsumosExport implements FromQuery, WithHeadings, WithMapping
{
    public function query()
    {
        return Insumo::query()->with(['sucursal', 'categoriaInsumo']);
    }

    public function headings(): array
    {
        return [
            'Nombre',
            'Descripcion',
            'Precio Venta',
            'Stock Actual',
            'Stock Minimo',
            'Estado',
            'Sucursal',
            'Categoria',
        ];
    }

    public function map($insumo): array
    {
        return [
            $insumo->nombre,
            $insumo->descripcion,
            $insumo->precio_venta,
            $insumo->stock_actual,
            $insumo->stock_minimo,
            $insumo->estado,
            $insumo->sucursal?->nombre,
            $insumo->categoriaInsumo?->nombre,
        ];
    }
}

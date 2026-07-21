<?php

namespace App\Exports;

use App\Models\Raza;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class RazasExport implements FromQuery, WithHeadings, WithMapping
{
    public function query()
    {
        return Raza::query()->with('especie');
    }

    public function headings(): array
    {
        return ['Nombre', 'Descripcion', 'Especie'];
    }

    public function map($raza): array
    {
        return [
            $raza->nombre,
            $raza->descripcion,
            $raza->especie?->nombre,
        ];
    }
}

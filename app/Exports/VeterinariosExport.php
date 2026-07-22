<?php

namespace App\Exports;

use App\Models\Veterinario;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class VeterinariosExport implements FromQuery, WithHeadings, WithMapping
{
    public function query()
    {
        return Veterinario::query()->with(['usuario', 'sucursal', 'especialidad']);
    }

    public function headings(): array
    {
        return [
            'Nombre',
            'Email',
            'Telefono',
            'Direccion',
            'Sucursal',
            'Especialidad',
        ];
    }

    public function map($veterinario): array
    {
        return [
            $veterinario->usuario?->name,
            $veterinario->usuario?->email,
            $veterinario->telefono,
            $veterinario->direccion,
            $veterinario->sucursal?->nombre,
            $veterinario->especialidad?->nombre,
        ];
    }
}

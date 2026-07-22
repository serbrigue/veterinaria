<?php

namespace App\Exports;

use App\Models\Mascota;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class MascotasExport implements FromQuery, WithHeadings, WithMapping
{
    public function query()
    {
        return Mascota::query()->with(['cliente.usuario', 'raza.especie']);
    }

    public function headings(): array
    {
        return [
            'Nombre',
            'Sexo',
            'Fecha Nacimiento',
            'Peso (kg)',
            'Color',
            'Esterilizado',
            'Raza',
            'Especie',
            'Cliente Email',
        ];
    }

    public function map($mascota): array
    {
        return [
            $mascota->nombre,
            $mascota->sexo,
            $mascota->fecha_nacimiento?->format('Y-m-d'),
            $mascota->peso_kg,
            $mascota->color,
            $mascota->esterilizado ? 'Sí' : 'No',
            $mascota->raza?->nombre,
            $mascota->raza?->especie?->nombre,
            $mascota->cliente?->usuario?->email,
        ];
    }
}

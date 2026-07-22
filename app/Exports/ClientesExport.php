<?php

namespace App\Exports;

use App\Models\Cliente;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ClientesExport implements FromQuery, WithHeadings, WithMapping
{
    public function query()
    {
        return Cliente::query()->with('usuario');
    }

    public function headings(): array
    {
        return ['Nombre', 'Email', 'Telefono', 'Direccion'];
    }

    public function map($cliente): array
    {
        return [
            $cliente->usuario?->name,
            $cliente->usuario?->email,
            $cliente->telefono,
            $cliente->direccion,
        ];
    }
}

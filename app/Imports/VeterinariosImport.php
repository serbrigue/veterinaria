<?php

namespace App\Imports;

use App\Models\Veterinario;
use App\Models\User;
use App\Models\Rol;
use App\Models\Especialidad;
use App\Models\Sucursal;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class VeterinariosImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        $email = $row['email'] ?? null;
        if (!$email) return null;

        $rol = Rol::where('nombre_interno', 'veterinario')->first();
        
        $user = User::firstOrCreate(
            ['email' => $email],
            [
                'name' => $row['nombre'] ?? 'Veterinario',
                'password' => Hash::make(Str::random(12)),
                'rol_id' => $rol ? $rol->id : null,
            ]
        );

        $especialidad = Especialidad::where('nombre', 'like', "%{$row['especialidad']}%")->first();
        $sucursal = Sucursal::where('nombre', 'like', "%{$row['sucursal']}%")->first();

        return Veterinario::updateOrCreate(
            ['user_id' => $user->id],
            [
                'telefono' => $row['telefono'] ?? null,
                'direccion' => $row['direccion'] ?? null,
                'especialidad_id' => $especialidad ? $especialidad->id : null,
                'sucursal_id' => $sucursal ? $sucursal->id : null,
            ]
        );
    }
}

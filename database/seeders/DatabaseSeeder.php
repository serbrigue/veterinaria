<?php

namespace Database\Seeders;

use App\Models\Mascota;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $usuario = User::firstOrCreate(
            ['email' => 'demo@veterinaria.test'],
            [
                'name' => 'Usuario Demo',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );

        Mascota::firstOrCreate(
            ['user_id' => $usuario->id, 'nombre' => 'Luna'],
            [
                'descripcion' => 'Gata doméstica, tranquila',
                'sexo' => 'hembra',
                'fecha_nacimiento' => '2021-03-15',
                'peso_kg' => 4.2,
                'color' => 'Gris',
                'esterilizado' => true,
            ]
        );

        Mascota::firstOrCreate(
            ['user_id' => $usuario->id, 'nombre' => 'Rocky'],
            [
                'descripcion' => 'Perro mediano, activo',
                'sexo' => 'macho',
                'fecha_nacimiento' => '2019-08-20',
                'peso_kg' => 12.5,
                'color' => 'Marrón',
                'esterilizado' => false,
            ]
        );
    }
}

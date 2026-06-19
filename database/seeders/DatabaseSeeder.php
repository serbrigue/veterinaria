<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Sucursal;
use App\Models\Box;
use App\Models\Especialidad;
use App\Models\Especie;
use App\Models\Raza;
use App\Models\Veterinario;
use App\Models\Cliente;
use App\Models\Mascota;
use App\Models\Cita;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Carbon;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Crear el Administrador Supremo
        $admin = User::create([
            'name' => 'Administrador General',
            'email' => 'admin@prueba.com',
            'password' => Hash::make('password123'),
            'rol' => 'admin',
        ]);

        // 2. Crear Especialidades Médicas
        $especialidadCardiologia = Especialidad::create([
            'nombre' => 'Cardiología',
            'descripcion' => 'Especialidad en enfermedades cardiovasculares de animales domésticos.',
        ]);

        $especialidadCirugia = Especialidad::create([
            'nombre' => 'Cirugía General',
            'descripcion' => 'Procedimientos quirúrgicos y de tejidos blandos.',
        ]);

        // 3. Crear Sucursales y sus respectivos Boxes
        $sucursalVina = Sucursal::create([
            'nombre' => 'Sede Central Viña del Mar',
            'direccion' => 'Av. Libertad 123, Viña del Mar',
            'telefono' => '+56912345678',
        ]);

        $box1 = Box::create([
            'nombre' => 'Box de Consulta 1',
            'descripcion' => 'Box destinado a consultas generales de felinos y caninos.',
            'sucursal_id' => $sucursalVina->id,
        ]);

        $box2 = Box::create([
            'nombre' => 'Box Quirófano A',
            'descripcion' => 'Sala de cirugías estéril con equipamiento de anestesia.',
            'sucursal_id' => $sucursalVina->id,
        ]);

        // 4. Crear Especies y Razas
        $especieCanino = Especie::create([
            'nombre' => 'Caninos',
            'descripcion' => 'Perros de todas las razas y tamaños.',
            'creado_por' => $admin->id,
        ]);

        $especieFelino = Especie::create([
            'nombre' => 'Felinos',
            'descripcion' => 'Gatos domésticos y otras especies felinas.',
            'creado_por' => $admin->id,
        ]);

        $razaPug = Raza::create([
            'nombre' => 'Pug',
            'descripcion' => 'Raza canina de aspecto pequeño y braquicéfalo.',
            'especie_id' => $especieCanino->id,
            'creado_por' => $admin->id,
        ]);

        $razaPersa = Raza::create([
            'nombre' => 'Persa',
            'descripcion' => 'Raza felina de pelo largo y cara ancha.',
            'especie_id' => $especieFelino->id,
            'creado_por' => $admin->id,
        ]);

        // 5. Crear Veterinario y su Perfil Clínico
        $userVet = User::create([
            'name' => 'Dra. Ana López',
            'email' => 'vet@prueba.com',
            'password' => Hash::make('password123'),
            'rol' => 'veterinario',
        ]);

        $veterinarioObj = Veterinario::create([
            'user_id' => $userVet->id,
            'sucursal_id' => $sucursalVina->id,
            'especialidad_id' => $especialidadCardiologia->id,
            'telefono' => '+56987654321',
            'direccion' => 'Calle Los Arrayanes 456, Viña del Mar',
        ]);

        // 6. Crear Cliente y su Perfil
        $userCliente = User::create([
            'name' => 'Carlos Pérez',
            'email' => 'user@prueba.com',
            'password' => Hash::make('password123'),
            'rol' => 'cliente',
        ]);

        $clienteObj = Cliente::create([
            'user_id' => $userCliente->id,
            'telefono' => '+56911223344',
            'direccion' => 'Calle Limache 789, Viña del Mar',
        ]);

        // 7. Crear Mascota asociada al Cliente
        $mascotaPersa = Mascota::create([
            'nombre' => 'Garfield',
            'descripcion' => 'Gato gordo y naranja muy dormilón.',
            'sexo' => 'Macho',
            'fecha_nacimiento' => Carbon::create(2020, 5, 15),
            'peso_kg' => 6.20,
            'color' => 'Naranja atigrado',
            'esterilizado' => true,
            'cliente_id' => $clienteObj->id,
            'raza_id' => $razaPersa->id,
        ]);

        // 8. Crear Cita para probar la integración completa
        Cita::create([
            'titulo' => 'Chequeo Cardiológico de Control',
            'descripcion' => 'Paciente presenta fatiga leve al correr. Control general por edad.',
            'fecha_hora' => Carbon::now()->addDays(2)->setTime(10, 0, 0),
            'hora_termino' => Carbon::now()->addDays(2)->setTime(11, 0, 0),
            'estado' => 'pendiente',
            'notas' => 'Traer exámenes previos de ecocardiograma.',
            'mascota_id' => $mascotaPersa->id,
            'veterinario_id' => $veterinarioObj->id,
            'box_id' => $box1->id,
        ]);

        $this->command->info('¡Base de datos sembrada y relaciones validadas exitosamente!');
    }
}
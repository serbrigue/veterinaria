<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Rol;
use App\Models\Permiso;
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
        $table = 'Rol';
        // 0. Crear Roles Físicos
        $rolAdmin = Rol::create([
            'nombre_interno' => 'admin',
            'nombre_legible' => 'Administrador Supremo',
        ]);

        $rolVet = Rol::create([
            'nombre_interno' => 'veterinario',
            'nombre_legible' => 'Personal Veterinario',
        ]);

        $rolCliente = Rol::create([
            'nombre_interno' => 'cliente',
            'nombre_legible' => 'Cliente / Propietario',
        ]);

        // 1. Crear Permisos del Sistema
        // Permisos de Cliente
        $permisoVerMisMascotas = Permiso::create([
            'nombre' => 'ver-mis-mascotas',
            'descripcion' => 'Permite visualizar el listado de mascotas propias.',
        ]);

        $permisoCrearMisMascotas = Permiso::create([
            'nombre' => 'crear-mis-mascotas',
            'descripcion' => 'Permite registrar una nueva mascota bajo su propiedad.',
        ]);

        $permisoEditarMisMascotas = Permiso::create([
            'nombre' => 'editar-mis-mascotas',
            'descripcion' => 'Permite editar la ficha de datos de una mascota propia.',
        ]);

        $permisoEliminarMisMascotas = Permiso::create([
            'nombre'=> 'eliminar-mis-mascotas',
            'descripcion'=> 'Permite eliminar la ficha de datos de una mascota propia.',
        ]);


        // Permisos de Veterinario
        $permisoVerMascotasSucursal = Permiso::create([
            'nombre' => 'ver-mascotas-sucursal',
            'descripcion' => 'Permite ver los datos de mascotas atendidas en su sucursal de pertenencia.',
        ]);

        $permisoEditarMascotasSucursal = Permiso::create([
            'nombre' => 'editar-mascotas-sucursal',
            'descripcion' => 'Permite modificar fichas e historiales de mascotas atendidas en su sucursal.',
        ]);

        $permisoCrearRazas = Permiso::create([
            'nombre' => 'crear-razas',
            'descripcion' => 'Permite crear razas.',
        ]);

        $permisoEditarRazas = Permiso::create([
            'nombre' => 'editar-razas',
            'descripcion' => 'Permite editar razas.',
        ]);

        $permisoEliminarRazas = Permiso::create([
            'nombre' => 'eliminar-razas',
            'descripcion' => 'Permite eliminar razas.',
        ]);

        $permisoCrearRecetas = Permiso::create([
            'nombre' => 'crear-recetas',
            'descripcion' => 'Permite prescribir recetas médicas a pacientes.',
        ]);

        $permisoGestionarCitasSucursal = Permiso::create([
            'nombre' => 'gestionar-citas-sucursal',
            'descripcion' => 'Permite agendar, reprogramar o cancelar citas en los boxes de su sucursal.',
        ]);

        $permisoCrearEspecies = Permiso::create([
            'nombre' => 'crear-especies',
            'descripcion' => 'Permite registrar nuevas especies en el catálogo.',
        ]);

        $permisoEditarEspecies = Permiso::create([
            'nombre' => 'editar-especies',
            'descripcion' => 'Permite editar la información de las especies del catálogo.',
        ]);

        $permisoEliminarEspecies = Permiso::create([
            'nombre' => 'eliminar-especies',
            'descripcion' => 'Permite eliminar especies del catálogo.',
        ]);

        $permisoCrearMisCitas = Permiso::create([
            'nombre' => 'agendar-cita',
            'descripcion' => 'Permite agendar citas para mascotas propias.',
        ]);

        $permisoVerMisCitas = Permiso::create([
            'nombre' => 'ver-mis-citas',
            'descripcion' => 'Permite ver las citas propias.',
        ]);

        $permisoEditarMisCitas = Permiso::create([
            'nombre' => 'editar-mis-citas',
            'descripcion'=> 'Permite editar las citas propias.',
        ]);

        $permisoEliminarMisCitas = Permiso::create([
            'nombre'=> 'eliminar-mis-citas',
            'descripcion'=> 'Permite eliminar las citas propias.',
        ]);


        $permisoVerCitasSucursal = Permiso::create([
            'nombre' => 'ver-citas-sucursal',
            'descripcion' => 'Permite ver las citas de su sucursal.',
        ]);

        $permisoEditarCitasSucursal = Permiso::create([
            'nombre' => 'editar-citas-sucursal',
            'descripcion' => 'Permite editar las citas de su sucursal.',
        ]);

        $permisoEliminarCitasSucursal = Permiso::create([
            'nombre' => 'eliminar-citas-sucursal',
            'descripcion' => 'Permite eliminar las citas de su sucursal.',
        ]);



        // 2. Asociar Permisos a los Roles correspondientes (Tablas pivote)
        // Asociar permisos a Cliente
        $rolCliente->permisos()->attach([
            $permisoVerMisMascotas->id,
            $permisoCrearMisMascotas->id,
            $permisoEditarMisMascotas->id,
            $permisoEliminarMisMascotas->id,
            $permisoCrearMisCitas->id,
            $permisoVerMisCitas->id,
            $permisoEditarMisCitas->id,
            $permisoEliminarMisCitas->id,
        ]);

        // Asociar permisos a Veterinario
        $rolVet->permisos()->attach([
            $permisoVerMascotasSucursal->id,
            $permisoEditarMascotasSucursal->id,
            $permisoCrearRecetas->id,
            $permisoGestionarCitasSucursal->id,
            $permisoCrearEspecies->id,
            $permisoEditarEspecies->id,
            $permisoEliminarEspecies->id,
            $permisoCrearRazas->id,
            $permisoEditarRazas->id,
            $permisoEliminarRazas->id,
            $permisoVerCitasSucursal->id,
            $permisoEditarCitasSucursal->id,
            $permisoEliminarCitasSucursal->id,
        ]);

        // 3. Crear el Administrador Supremo
        $admin = User::create([
            'name' => 'Administrador General',
            'email' => 'admin@prueba.com',
            'password' => Hash::make('password123'),
            'rol_id' => $rolAdmin->id, // Relación de rol normalizado
        ]);

        // 4. Crear Especialidades Médicas
        $especialidadCardiologia = Especialidad::create([
            'nombre' => 'Cardiología',
            'descripcion' => 'Especialidad en enfermedades cardiovasculares de animales domésticos.',
        ]);

        $especialidadCirugia = Especialidad::create([
            'nombre' => 'Cirugía General',
            'descripcion' => 'Procedimientos quirúrgicos y de tejidos blandos.',
        ]);

        // 5. Crear Sucursales y sus respectivos Boxes
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

        // 6. Crear Especies y Razas
        $especieCanino = Especie::create([
            'nombre' => 'Caninos',
            'descripcion' => 'Perros de todas las razas y tamaños.',
            'creado_por' => $admin->id,
            'imagen_url' => 'https://i.pinimg.com/736x/bd/24/5e/bd245e2bb67ba4879cf54fa736768792.jpg',
        ]);

        $especieFelino = Especie::create([
            'nombre' => 'Felinos',
            'descripcion' => 'Gatos domésticos y otras especies felinas.',
            'creado_por' => $admin->id,
            'imagen_url' => 'https://i.pinimg.com/236x/d7/9d/cb/d79dcbfe0e185dbcb4ae2d4422889d62.jpg',
        ]);

        $razaPug = Raza::create([
            'nombre' => 'Pug',
            'descripcion' => 'Raza canina de aspecto pequeño y braquicéfalo.',
            'especie_id' => $especieCanino->id,
            'creado_por' => $admin->id,
            'imagen_url' => 'https://i.pinimg.com/webp87/736x/61/37/a1/6137a1feae4f909f89cc0a1ac3693c3f.webp',
        ]);

        $razaPersa = Raza::create([
            'nombre' => 'Persa',
            'descripcion' => 'Raza felina de pelo largo y cara ancha.',
            'especie_id' => $especieFelino->id,
            'creado_por' => $admin->id,
            'imagen_url' =>'https://i.pinimg.com/736x/f7/4b/92/f74b9222fa0da54389bcc25cdb61ce3f.jpg',
        ]);

        // 7. Crear Veterinario y su Perfil Clínico
        $userVet = User::create([
            'name' => 'Dra. Ana López',
            'email' => 'vet@prueba.com',
            'password' => Hash::make('password123'),
            'rol_id' => $rolVet->id, // Relación de rol normalizado
        ]);

        $veterinarioObj = Veterinario::create([
            'user_id' => $userVet->id,
            'sucursal_id' => $sucursalVina->id,
            'especialidad_id' => $especialidadCardiologia->id,
            'telefono' => '+56987654321',
            'direccion' => 'Calle Los Arrayanes 456, Viña del Mar',
        ]);

        // 8. Crear Cliente y su Perfil
        $userCliente = User::create([
            'name' => 'Carlos Pérez',
            'email' => 'user@prueba.com',
            'password' => Hash::make('password123'),
            'rol_id' => $rolCliente->id, // Relación de rol normalizado
        ]);

        $clienteObj = Cliente::create([
            'user_id' => $userCliente->id,
            'telefono' => '+56911223344',
            'direccion' => 'Calle Limache 789, Viña del Mar',
        ]);

        // 9. Crear Mascota asociada al Cliente
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

        // 10. Crear Cita para probar la integración completa
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

        $this->command->info('¡Base de datos sembrada y relaciones validadas exitosamente con roles y permisos normalizados!');
    }
}
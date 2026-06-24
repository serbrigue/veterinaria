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
use Illuminate\Support\Facades\DB;

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
            'nombre' => 'eliminar-mis-mascotas',
            'descripcion' => 'Permite eliminar la ficha de datos de una mascota propia.',
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
            'descripcion' => 'Permite editar las citas propias.',
        ]);

        $permisoEliminarMisCitas = Permiso::create([
            'nombre' => 'eliminar-mis-citas',
            'descripcion' => 'Permite eliminar las citas propias.',
        ]);

        $permisoPagarTransacciones = Permiso::create([
            'nombre' => 'pagar-transacciones',
            'descripcion' => 'Permite realizar el pago en línea de las transacciones pendientes.',
        ]);


        $permisoVerCitasSucursal = Permiso::create([
            'nombre' => 'ver-citas-sucursal',
            'descripcion' => 'Permite ver las citas de su sucursal.',
        ]);

        $permisoEditarCitasSucursal = Permiso::create([
            'nombre' => 'editar-citas-sucursal',
            'descripcion' => 'Permite editar las citas de su sucursal.',
        ]);

        $permisoGestionarCargosSucursal = Permiso::create([
            'nombre' => 'gestionar-cargos-sucursal',
            'descripcion' => 'Permite agregar, actualizar y eliminar cargos (insumos y prestaciones) en citas.',
        ]);

        $permisoEliminarCitasSucursal = Permiso::create([
            'nombre' => 'eliminar-citas-sucursal',
            'descripcion' => 'Permite eliminar las citas de su sucursal.',
        ]);

        // Permisos Clientes
        $permisoVerClientes = Permiso::create([
            'nombre' => 'ver-clientes',
            'descripcion' => 'Permite acceder a la vista de clientes.',
        ]);
        $permisoEditarClientes = Permiso::create([
            'nombre' => 'editar-clientes',
            'descripcion' => 'Permite editar información de clientes.',
        ]);

        // Permisos Sucursales
        $permisoVerSucursales = Permiso::create([
            'nombre' => 'ver-sucursales',
            'descripcion' => 'Permite acceder a la vista de sucursales.',
        ]);
        $permisoCrearSucursales = Permiso::create([
            'nombre' => 'crear-sucursales',
            'descripcion' => 'Permite registrar sucursales.',
        ]);
        $permisoEditarSucursales = Permiso::create([
            'nombre' => 'editar-sucursales',
            'descripcion' => 'Permite editar sucursales.',
        ]);
        $permisoEliminarSucursales = Permiso::create([
            'nombre' => 'eliminar-sucursales',
            'descripcion' => 'Permite eliminar sucursales.',
        ]);

        // Permisos Boxes
        $permisoVerBoxes = Permiso::create([
            'nombre' => 'ver-boxes',
            'descripcion' => 'Permite acceder a la vista de boxes.',
        ]);
        $permisoCrearBoxes = Permiso::create([
            'nombre' => 'crear-boxes',
            'descripcion' => 'Permite registrar boxes.',
        ]);
        $permisoEditarBoxes = Permiso::create([
            'nombre' => 'editar-boxes',
            'descripcion' => 'Permite editar boxes.',
        ]);
        $permisoEliminarBoxes = Permiso::create([
            'nombre' => 'eliminar-boxes',
            'descripcion' => 'Permite eliminar boxes.',
        ]);

        // Permisos Catálogos
        $permisoVerInsumos = Permiso::create([
            'nombre' => 'ver-insumos',
            'descripcion' => 'Permite ver el catálogo de insumos de su sucursal.',
        ]);
        $permisoVerPrestaciones = Permiso::create([
            'nombre' => 'ver-prestaciones',
            'descripcion' => 'Permite ver el catálogo de prestaciones médicas.',
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
            $permisoPagarTransacciones->id,
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
            $permisoVerClientes->id,
            $permisoEditarClientes->id,
            $permisoVerSucursales->id,
            $permisoCrearSucursales->id,
            $permisoEditarSucursales->id,
            $permisoEliminarSucursales->id,
            $permisoVerBoxes->id,
            $permisoCrearBoxes->id,
            $permisoEditarBoxes->id,
            $permisoEliminarBoxes->id,
            $permisoVerInsumos->id,
            $permisoVerPrestaciones->id,
            $permisoGestionarCargosSucursal->id,
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

        $especialidadPeluqueria = Especialidad::create([
            'nombre' => 'Peluquería',
            'descripcion' => 'Procedimientos de peluquería.',
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

        $sucursalValparaiso = Sucursal::create([
            'nombre' => 'Sede Valparaiso',
            'direccion' => 'Av. Errazuriz 1234, Valparaiso',
            'telefono' => '+56987654321',
        ]);

        $box3 = Box::create([
            'nombre' => 'Box de Consulta Valparaiso',
            'descripcion' => 'Box de atención general.',
            'sucursal_id' => $sucursalValparaiso->id,
        ]);

        $box4 = Box::create([
            'nombre' => 'Box Peluquería',
            'descripcion' => 'Box de peluquería.',
            'sucursal_id' => $sucursalValparaiso->id,
        ]);

        // 4.5 Crear Catálogo de Prestaciones e Insumos
        $prestacionGeneralId = DB::table('prestaciones')->insertGetId([
            'nombre' => 'Consulta General',
            'descripcion' => 'Evaluación clínica completa del paciente.',
            'precio_base' => 20000.00,
            'sucursal_id' => $sucursalVina->id,
            'especialidad_id' => null,
            'comision_vet' => 50.00, // 50%
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        $prestacionCardioId = DB::table('prestaciones')->insertGetId([
            'nombre' => 'Ecocardiograma',
            'descripcion' => 'Ultrasonido del corazón.',
            'precio_base' => 45000.00,
            'sucursal_id' => $sucursalVina->id,
            'especialidad_id' => $especialidadCardiologia->id,
            'comision_vet' => 60.00, // 60%
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        $prestacionCirugiaId = DB::table('prestaciones')->insertGetId([
            'nombre' => 'Cirugía Mayor',
            'descripcion' => 'Procedimiento quirúrgico con anestesia general.',
            'precio_base' => 150000.00,
            'sucursal_id' => $sucursalVina->id,
            'especialidad_id' => $especialidadCirugia->id,
            'comision_vet' => 40.00, // 40%
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        $prestacionGeneralIdValpo = DB::table('prestaciones')->insertGetId([
            'nombre' => 'Consulta General',
            'descripcion' => 'Evaluación clínica completa del paciente.',
            'precio_base' => 20000.00,
            'sucursal_id' => $sucursalValparaiso->id,
            'especialidad_id' => null,
            'comision_vet' => 50.00, // 50%
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        $prestacionPeluqueriaIdValpo = DB::table('prestaciones')->insertGetId([
            'nombre' => 'Corte de pelo',
            'descripcion' => 'Corte de pelo para perro o gato.',
            'precio_base' => 15000.00,
            'sucursal_id' => $sucursalValparaiso->id,
            'especialidad_id' => $especialidadPeluqueria->id,
            'comision_vet' => 50.00, // 50%
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        $prestacionCastracionIdValpo = DB::table('prestaciones')->insertGetId([
            'nombre' => 'Castración',
            'descripcion' => 'Castración para perro o gato.',
            'precio_base' => 15000.00,
            'sucursal_id' => $sucursalValparaiso->id,
            'especialidad_id' => $especialidadCirugia->id,
            'comision_vet' => 50.00, // 50%
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        $insumoAnestesiaId = DB::table('insumos')->insertGetId([
            'nombre' => 'Anestesia General (ml)',
            'descripcion' => 'Anestésico inyectable.',
            'precio_venta' => 5000.00,
            'sucursal_id' => $sucursalVina->id,
            'stock_actual' => 100,
            'stock_minimo' => 20,
            'estado' => 'activo',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        $insumoJeringaId = DB::table('insumos')->insertGetId([
            'nombre' => 'Jeringa 5ml estéril',
            'descripcion' => 'Jeringa desechable.',
            'precio_venta' => 500.00,
            'sucursal_id' => $sucursalVina->id,
            'stock_actual' => 500,
            'stock_minimo' => 50,
            'estado' => 'activo',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        // Catálogo Sucursal Providencia
        DB::table('prestaciones')->insert([
            'nombre' => 'Ecografía Abdominal',
            'descripcion' => 'Examen por imagen no invasivo.',
            'precio_base' => 35000.00,
            'sucursal_id' => $sucursalValparaiso->id,
            'especialidad_id' => null,
            'comision_vet' => 55.00,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        DB::table('insumos')->insert([
            'nombre' => 'Vendas Elásticas',
            'descripcion' => 'Rollo de venda de 5cm.',
            'precio_venta' => 1200.00,
            'sucursal_id' => $sucursalValparaiso->id,
            'stock_actual' => 45,
            'stock_minimo' => 10,
            'estado' => 'activo',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
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

        $especieAve = Especie::create([
            'nombre' => 'Aves',
            'descripcion' => 'Aves domésticas y exóticas.',
            'creado_por' => $admin->id,
            'imagen_url' => 'https://i.pinimg.com/736x/5d/c6/1b/5dc61bc7b92d92e0cbce81dd5816b143.jpg',
        ]);

        $especieRoedor = Especie::create([
            'nombre' => 'Roedores',
            'descripcion' => 'Hámsters, cobayas, chinchillas, etc.',
            'creado_por' => $admin->id,
            'imagen_url' => 'https://i.pinimg.com/736x/96/69/87/966987a0588593eeb74c55732aad0647.jpg',
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
            'imagen_url' => 'https://i.pinimg.com/736x/f7/4b/92/f74b9222fa0da54389bcc25cdb61ce3f.jpg',
        ]);

        $razaGolden = Raza::create([
            'nombre' => 'Golden Retriever',
            'descripcion' => 'Perro de raza grande, amigable y familiar.',
            'especie_id' => $especieCanino->id,
            'creado_por' => $admin->id,
            'imagen_url' => 'https://i.pinimg.com/736x/23/cf/fd/23cffd3e0a849b971ca8ccc2e61a1032.jpg',
        ]);

        $razaSiames = Raza::create([
            'nombre' => 'Siamés',
            'descripcion' => 'Gato oriental con ojos azules distintivos.',
            'especie_id' => $especieFelino->id,
            'creado_por' => $admin->id,
            'imagen_url' => 'https://i.pinimg.com/736x/8b/49/79/8b4979bb9e40c3bc0020070e2bab1fa1.jpg',
        ]);

        $razaCanario = Raza::create([
            'nombre' => 'Canario',
            'descripcion' => 'Pequeño pájaro cantor.',
            'especie_id' => $especieAve->id,
            'creado_por' => $admin->id,
            'imagen_url' => 'https://i.pinimg.com/736x/a0/9b/5e/a09b5e53113b50492091aeb6a4c5fae2.jpg',
        ]);

        $razaHamster = Raza::create([
            'nombre' => 'Hámster Sirio',
            'descripcion' => 'Hámster común de tamaño mediano.',
            'especie_id' => $especieRoedor->id,
            'creado_por' => $admin->id,
            'imagen_url' => 'https://i.pinimg.com/736x/af/62/bf/af62bfb98458f1a4b975499a88b1a0dd.jpg',
        ]);

        // 7. Crear Veterinarios y sus Perfiles Clínicos
        $userVet1 = User::create([
            'name' => 'Dra. Ana López',
            'email' => 'vet@prueba.com',
            'password' => Hash::make('password123'),
            'rol_id' => $rolVet->id,
        ]);
        $veterinario1 = Veterinario::create([
            'user_id' => $userVet1->id,
            'sucursal_id' => $sucursalVina->id,
            'especialidad_id' => $especialidadCardiologia->id,
            'telefono' => '+56987654321',
            'direccion' => 'Calle Los Arrayanes 456, Viña del Mar',
            'foto_perfil_url' => 'https://i.pinimg.com/736x/a1/ec/99/a1ec99f8b73d85ec3de20240446b0010.jpg',
        ]);

        $userVet2 = User::create([
            'name' => 'Dr. Roberto Sánchez',
            'email' => 'roberto@prueba.com',
            'password' => Hash::make('password123'),
            'rol_id' => $rolVet->id,
        ]);
        $veterinario2 = Veterinario::create([
            'user_id' => $userVet2->id,
            'sucursal_id' => $sucursalVina->id,
            'especialidad_id' => $especialidadCirugia->id,
            'telefono' => '+56911223344',
            'direccion' => 'Av. San Martín 123, Viña del Mar',
            'foto_perfil_url' => 'https://i.pinimg.com/736x/21/de/81/21de81317ba7d8c6e4fc66a186468f65.jpg',
        ]);

        $userVet3 = User::create([
            'name' => 'Dr. Pedro Morales',
            'email' => 'pedro@prueba.com',
            'password' => Hash::make('password123'),
            'rol_id' => $rolVet->id,
        ]);
        $veterinario3 = Veterinario::create([
            'user_id' => $userVet3->id,
            'sucursal_id' => $sucursalValparaiso->id,
            'especialidad_id' => $especialidadPeluqueria->id,
            'telefono' => '+56933445566',
            'direccion' => 'Calle Victoria 234, Valparaíso',
            'foto_perfil_url' => 'https://i.pinimg.com/736x/94/b1/7b/94b17b31dcc9f4442bf85a6062695aee.jpg',
        ]);

        $userVet4 = User::create([
            'name' => 'Dra. Camila Rojas',
            'email' => 'camila@prueba.com',
            'password' => Hash::make('password123'),
            'rol_id' => $rolVet->id,
        ]);
        $veterinario4 = Veterinario::create([
            'user_id' => $userVet4->id,
            'sucursal_id' => $sucursalValparaiso->id,
            'especialidad_id' => $especialidadCirugia->id,
            'telefono' => '+56988776655',
            'direccion' => 'Cerro Carcel 12, Valparaíso',
            'foto_perfil_url' => 'https://i.pinimg.com/736x/cc/0a/5a/cc0a5aa20adc32aaadc37913cbaa0f03.jpg',
        ]);

        // 8. Crear Clientes y sus Perfiles
        $userCliente1 = User::create([
            'name' => 'Carlos Pérez',
            'email' => 'user@prueba.com',
            'password' => Hash::make('password123'),
            'rol_id' => $rolCliente->id,
        ]);
        $cliente1 = Cliente::create([
            'user_id' => $userCliente1->id,
            'telefono' => '+56911223344',
            'direccion' => 'Calle Limache 789, Viña del Mar',
            'foto_perfil_url' => 'https://i.pinimg.com/736x/33/74/31/3374315482f8552b98b7e2d86b69cc79.jpg',
        ]);

        $userCliente2 = User::create([
            'name' => 'María González',
            'email' => 'maria@prueba.com',
            'password' => Hash::make('password123'),
            'rol_id' => $rolCliente->id,
        ]);
        $cliente2 = Cliente::create([
            'user_id' => $userCliente2->id,
            'telefono' => '+56955667788',
            'direccion' => 'Cerro Alegre 45, Valparaíso',
            'foto_perfil_url' => 'https://i.pinimg.com/736x/a9/9b/e0/a99be01455e9c86de10ed8a49e58c462.jpg',
        ]);

        // 9. Crear Mascotas asociadas a los Clientes
        $mascota1 = Mascota::create([
            'nombre' => 'Garfield',
            'descripcion' => 'Gato gordo y naranja muy dormilón.',
            'sexo' => 'Macho',
            'fecha_nacimiento' => Carbon::create(2020, 5, 15),
            'peso_kg' => 6.20,
            'color' => 'Naranja atigrado',
            'esterilizado' => true,
            'cliente_id' => $cliente1->id,
            'raza_id' => $razaPersa->id,
            'imagen_url' => 'https://i.pinimg.com/736x/f2/38/0d/f2380dee74e4635c58a49d4aa469a78a.jpg'
        ]);

        $mascota2 = Mascota::create([
            'nombre' => 'Firulais',
            'descripcion' => 'Perro muy activo, le gusta morder zapatos.',
            'sexo' => 'Macho',
            'fecha_nacimiento' => Carbon::create(2022, 10, 1),
            'peso_kg' => 8.50,
            'color' => 'Beige',
            'esterilizado' => false,
            'cliente_id' => $cliente2->id,
            'raza_id' => $razaPug->id,
            'imagen_url' => 'https://i.pinimg.com/736x/2f/6f/26/2f6f26a6f20616cbe56ed3d522d1ac88.jpg'
        ]);

        $mascota3 = Mascota::create([
            'nombre' => 'Luna',
            'descripcion' => 'Gata arisca, no le gusta que la toquen.',
            'sexo' => 'Hembra',
            'fecha_nacimiento' => Carbon::create(2019, 3, 22),
            'peso_kg' => 4.10,
            'color' => 'Blanco',
            'esterilizado' => true,
            'cliente_id' => $cliente2->id,
            'raza_id' => $razaPersa->id,
            'imagen_url' => 'https://i.pinimg.com/736x/77/6a/bf/776abfe995882c4f5fa8e8146830648f.jpg'
        ]);

        // 10. Crear Citas Múltiples (Pasadas, Canceladas, Urgencias y Futuras)

        // Cita 1: Pasada y completada
        $citaVacuna = Cita::create([
            'titulo' => 'Vacunación Anual',
            'descripcion' => 'Vacunación óctuple y antirrábica.',
            'fecha_hora' => Carbon::now()->subDays(10)->setTime(10, 0, 0),
            'hora_termino' => Carbon::now()->subDays(10)->setTime(10, 30, 0),
            'estado' => 'completada',
            'notas' => 'Paciente reaccionó bien. Próxima vacuna en un año.',
            'mascota_id' => $mascota1->id,
            'veterinario_id' => $veterinario1->id,
            'box_id' => $box1->id,
            'prestacion_id' => $prestacionGeneralId,
            'tipo' => 'normal',
        ]);

        // Cita 2: Cancelada
        Cita::create([
            'titulo' => 'Consulta por cojera',
            'descripcion' => 'El perrito cojea de la pata trasera derecha.',
            'fecha_hora' => Carbon::now()->subDays(2)->setTime(15, 0, 0),
            'hora_termino' => Carbon::now()->subDays(2)->setTime(15, 30, 0),
            'estado' => 'cancelada',
            'notas' => 'El cliente canceló porque el perrito mejoró solo.',
            'mascota_id' => $mascota2->id,
            'veterinario_id' => $veterinario2->id,
            'box_id' => $box1->id,
            'prestacion_id' => $prestacionGeneralId,
            'tipo' => 'normal',
        ]);

        // Cita 3: Pendiente en el futuro (Dr. Ana)
        Cita::create([
            'titulo' => 'Chequeo Cardiológico de Control',
            'descripcion' => 'Paciente presenta fatiga leve al correr. Control general por edad.',
            'fecha_hora' => Carbon::now()->addDays(2)->setTime(10, 0, 0),
            'hora_termino' => Carbon::now()->addDays(2)->setTime(10, 30, 0),
            'estado' => 'pendiente',
            'notas' => 'Traer exámenes previos de ecocardiograma.',
            'mascota_id' => $mascota1->id,
            'veterinario_id' => $veterinario1->id,
            'box_id' => $box1->id,
            'prestacion_id' => $prestacionCardioId,
            'tipo' => 'normal',
        ]);

        // Cita 4: Urgencia en el futuro (Dr. Roberto - Quirófano)
        $citaCirugia = Cita::create([
            'titulo' => 'Cirugía extracción de cuerpo extraño',
            'descripcion' => 'Se tragó un juguete de goma. Atención fuera de horario.',
            'fecha_hora' => Carbon::now()->addDays(1)->setTime(19, 0, 0),
            'hora_termino' => Carbon::now()->addDays(1)->setTime(19, 30, 0),
            'estado' => 'pendiente',
            'notas' => 'Preparar quirófano para cirugía de urgencia.',
            'mascota_id' => $mascota2->id,
            'veterinario_id' => $veterinario2->id,
            'box_id' => $box2->id,
            'prestacion_id' => $prestacionCirugiaId,
            'tipo' => 'urgencia',
        ]);

        // Cita 5: Valparaiso Peluqueria
        $citaPeluqueria = Cita::create([
            'titulo' => 'Corte de Pelo',
            'descripcion' => 'Corte de pelo y baño.',
            'fecha_hora' => Carbon::now()->addDays(3)->setTime(11, 0, 0),
            'hora_termino' => Carbon::now()->addDays(3)->setTime(12, 0, 0),
            'estado' => 'pendiente',
            'notas' => 'Dejar bien perfumado.',
            'mascota_id' => $mascota3->id,
            'veterinario_id' => $veterinario3->id,
            'box_id' => $box4->id,
            'prestacion_id' => $prestacionPeluqueriaIdValpo,
            'tipo' => 'normal',
        ]);

        // 11. Cargar el Carrito de Compras (citas_cargo) para las citas de ejemplo

        // Cobro de la Vacunación (2 Jeringas)
        DB::table('citas_cargo')->insert([
            'cita_id' => $citaVacuna->id,
            'prestacion_id' => $citaVacuna->prestacion_id,
            'insumo_id' => $insumoJeringaId,
            'cantidad' => 2,
            'precio_unitario' => 500.00,
            'subtotal' => 1000.00,
            'pago_vet' => 0,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);



        DB::table('citas_cargo')->insert([
            'cita_id' => $citaCirugia->id,
            'prestacion_id' => $citaCirugia->prestacion_id,
            'insumo_id' => $insumoAnestesiaId,
            'cantidad' => 4,
            'precio_unitario' => 5000.00,
            'subtotal' => 20000.00,
            'pago_vet' => 0,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        // 12. Generar Transacción para Cita Completada
        \App\Models\Transaccion::create([
            'cita_id' => $citaVacuna->id,
            'cliente_id' => $cliente1->id,
            'monto_total' => 21000.00,
            'monto_pagado' => 21000.00,
            'estado' => 'pagado',
            'metodo_pago' => 'tarjeta',
            'fecha_pago' => Carbon::now()->subDays(10)->setTime(10, 30, 0),
        ]);

        $this->command->info('¡Base de datos sembrada y expandida exitosamente con transacciones!');
    }
}

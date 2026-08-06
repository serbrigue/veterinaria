<?php

namespace App\Policies;

use App\Models\Mascota;
use App\Models\User;

/*
|--------------------------------------------------------------------------
| Mascota Policy
|--------------------------------------------------------------------------
| Administrador: Acceso total automático a todas las acciones.
| Cliente: Puede ver, crear, editar y eliminar sus propias mascotas si tiene los permisos ('ver-mis-mascotas', 'crear-mis-mascotas', 'editar-mis-mascotas', 'eliminar-mis-mascotas').
| Secretaria: Puede ver, registrar, editar y eliminar mascotas de la sucursal si cuenta con los permisos correspondientes ('ver-mascotas-sucursal', 'editar-mascotas-sucursal').
| Veterinario: Solo puede ver el listado y detalles de las mascotas de la sucursal con el permiso 'ver-mascotas-sucursal'. No puede crear, editar ni eliminar mascotas.
*/
class MascotaPolicy
{
    // El filtro before se ejecuta antes de cualquier otro método de la Policy.
    // Si el usuario es administrador supremo, le otorgamos acceso total automático (bypass).
    public function before(User $user, string $ability)
    {
        if ($user->isAdmin()) {
            return true;
        }
    }

    // Verifica si el usuario tiene permiso para ver todas las mascotas
    public function verTodas(User $user): bool
    {
        // Un cliente necesita el permiso de ver sus propias mascotas
        if ($user->isCliente() && $user->tienePermiso('ver-mis-mascotas')) {
            return true;
        }

        // Un veterinario necesita el permiso de ver las de su sucursal
        if ($user->isVeterinario() && $user->tienePermiso('ver-mascotas-sucursal')) {
            return true;
        }

        // Una secretaria necesita el permiso de ver las de su sucursal
        if ($user->rol?->nombre_interno === 'secretaria' && $user->tienePermiso('ver-mascotas-sucursal')) {
            return true;
        }

        return false;
    }

    // Verifica si el usuario tiene permiso para ver los detalles de una mascota específica
    public function ver(User $user, Mascota $mascota): bool
    {
        // Si es cliente, solo puede ver la mascota si es el propietario (dueño)
        if ($user->isCliente()) {
            return $user->tienePermiso('ver-mis-mascotas') && $mascota->cliente_id === $user->cliente?->id;
        }

        // Si es veterinario
        if ($user->isVeterinario() && $user->tienePermiso('ver-mascotas-sucursal')) {
            return true;
        }

        // Si es secretaria
        if ($user->rol?->nombre_interno === 'secretaria' && $user->tienePermiso('ver-mascotas-sucursal')) {
            return true;
        }

        return false;
    }

    // Verifica si el usuario tiene permiso para crear una mascota
    public function crear(User $user): bool
    {
        // Clientes pueden crear sus propias mascotas
        if ($user->isCliente() && $user->tienePermiso('crear-mis-mascotas')) {
            return true;
        }

        // Secretarias pueden registrar mascotas de la sucursal
        if ($user->rol?->nombre_interno === 'secretaria' && $user->tienePermiso('editar-mascotas-sucursal')) {
            return true;
        }

        return false;
    }

    // Verifica si el usuario tiene permiso para editar una mascota específica
    public function editar(User $user, Mascota $mascota): bool
    {
        // Si es cliente, requiere el permiso de edición y ser el propietario
        if ($user->isCliente()) {
            return $user->tienePermiso('editar-mis-mascotas') && $mascota->cliente_id === $user->cliente?->id;
        }

        // Si es secretaria, requiere el permiso de edición de mascotas
        if ($user->rol?->nombre_interno === 'secretaria' && $user->tienePermiso('editar-mascotas-sucursal')) {
            return true;
        }

        return false;
    }

    // Verifica si el usuario tiene permiso para eliminar una mascota específica
    public function eliminar(User $user, Mascota $mascota): bool
    {
        // Si es cliente, requiere el permiso de eliminación y ser el propietario
        if ($user->isCliente()) {
            return $user->tienePermiso('eliminar-mis-mascotas') && $mascota->cliente_id === $user->cliente?->id;
        }

        // Si es secretaria, requiere el permiso de edición de mascotas
        if ($user->rol?->nombre_interno === 'secretaria' && $user->tienePermiso('editar-mascotas-sucursal')) {
            return true;
        }

        return false;
    }
}

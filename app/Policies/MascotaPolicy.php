<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Mascota;
use Illuminate\Auth\Access\Response;

class MascotaPolicy
{

    #El filtro before se ejecuta antes de cualquier otro método de la Policy.
    #Si el usuario es administrador supremo, le otorgamos acceso total automático (bypass).
    public function before(User $user, string $ability)
    {
        if ($user->isAdmin()) {
            return true;
        }
    }

    #Verifica si el usuario tiene permiso para ver todas las mascotas
    public function verTodas(User $user): bool
    {
        # Un cliente necesita el permiso de ver sus propias mascotas
        if ($user->isCliente() && $user->tienePermiso('ver-mis-mascotas')) {
            return true;
        }

        # Un veterinario necesita el permiso de ver las de su sucursal
        if ($user->isVeterinario() && $user->tienePermiso('ver-mascotas-sucursal')) {
            return true;
        }

        return false;
    }

    #Verifica si el usuario tiene permiso para ver los detalles de una mascota específica
    public function ver(User $user, Mascota $mascota): bool
    {
        # Si es cliente, solo puede ver la mascota si es el propietario (dueño)
        if ($user->isCliente()) {
            return $user->tienePermiso('ver-mis-mascotas') && $mascota->cliente_id === $user->cliente?->id;
        }

        # Si es veterinario, de momento requiere el permiso general de sucursal
        if ($user->isVeterinario()) {
            return $user->tienePermiso('ver-mascotas-sucursal');
        }

        return false;
    }

    #Verifica si el usuario tiene permiso para crear una mascota
    public function crear(User $user): bool
    {
        #Solo clientes pueden crear mascotas
        return $user->isCliente() && $user->tienePermiso('crear-mis-mascotas');
    }

    #Verifica si el usuario tiene permiso para editar una mascota específica
    public function editar(User $user, Mascota $mascota): bool

    {   #Si es cliente, requiere el permiso de edición y ser el propietario

        if ($user->isCliente()) {
            return $user->tienePermiso('editar-mis-mascotas') && $mascota->cliente_id === $user->cliente?->id;
        }

        #Si es veterinario, requiere el permiso de edición de su sucursal

        if ($user->isVeterinario()) {
            return $user->tienePermiso('editar-mascotas-sucursal');
        }

        return false;
    }

    #Verifica si el usuario tiene permiso para eliminar una mascota específica
    public function eliminar(User $user, Mascota $mascota): bool
    {
        # Solo clientes pueden eliminar sus mascotas
        return $user->isCliente()
            && $user->tienePermiso('eliminar-mis-mascotas')
            && $mascota->cliente_id === $user->cliente?->id;
    }
}

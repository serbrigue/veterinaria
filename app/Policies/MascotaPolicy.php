<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Mascota;
use Illuminate\Auth\Access\Response;

class MascotaPolicy
{
    /**
     * El filtro before se ejecuta antes de cualquier otro método de la Policy.
     * Si el usuario es administrador supremo, le otorgamos acceso total automático (bypass).
     */
    public function before(User $user, string $ability)
    {
        if ($user->isAdmin()) {
            return true;
        }
    }

    /**
     * Determina si el usuario puede ver el listado de mascotas.
     */
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

        return false;
    }

    /**
     * Determina si el usuario puede ver los detalles de una mascota específica.
     */
    public function ver(User $user, Mascota $mascota): bool
    {
        // Si es cliente, solo puede ver la mascota si es el propietario (dueño)
        if ($user->isCliente()) {
            return $user->tienePermiso('ver-mis-mascotas') && $mascota->cliente_id === $user->cliente?->id;
        }

        // Si es veterinario, de momento requiere el permiso general de sucursal
        if ($user->isVeterinario()) {
            return $user->tienePermiso('ver-mascotas-sucursal');
        }

        return false;
    }

    /**
     * Determina si el usuario puede registrar nuevas mascotas.
     */
    public function crear(User $user): bool
    {
        // En nuestro sembrado, solo los clientes (y admins a través del before) pueden registrar mascotas
        return $user->isCliente() && $user->tienePermiso('crear-mis-mascotas');
    }

    /**
     * Determina si el usuario puede actualizar una mascota específica.
     */
    public function editar(User $user, Mascota $mascota): bool
    {
        // Si es cliente, requiere el permiso de edición y ser el propietario
        if ($user->isCliente()) {
            return $user->tienePermiso('editar-mis-mascotas') && $mascota->cliente_id === $user->cliente?->id;
        }

        // Si es veterinario, requiere el permiso de edición de su sucursal
        if ($user->isVeterinario()) {
            return $user->tienePermiso('editar-mascotas-sucursal');
        }

        return false;
    }

    /**
     * Determina si el usuario puede eliminar una mascota específica.
     */

    public function eliminar(User $user, Mascota $mascota): bool
    {
        return $user->isCliente()
            && $user->tienePermiso('eliminar-mis-mascotas')
            && $mascota->cliente_id === $user->cliente?->id;
    }
}

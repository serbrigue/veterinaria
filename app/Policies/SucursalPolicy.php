<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Sucursal;
use Illuminate\Auth\Access\Response;

class SucursalPolicy
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

    public function verTodas(User $user): bool
    {
        return $user->isVeterinario() && $user->tienePermiso('ver-sucursales');
    }

    public function ver(User $user, Sucursal $sucursal): bool
    {
        return $user->isVeterinario() && $user->tienePermiso('ver-sucursales');
    }

    public function crear(User $user): bool
    {
        return $user->isVeterinario() && $user->tienePermiso('crear-sucursales');
    }

    public function editar(User $user, Sucursal $sucursal): bool
    {
        return $user->isVeterinario() && $user->tienePermiso('editar-sucursales');
    }

    public function eliminar(User $user, Sucursal $sucursal): bool
    {
        return $user->isVeterinario() && $user->tienePermiso('eliminar-sucursales');
    }
}

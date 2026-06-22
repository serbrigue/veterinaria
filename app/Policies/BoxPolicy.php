<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Box;
use Illuminate\Auth\Access\Response;

class BoxPolicy
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
        return $user->isVeterinario() && $user->tienePermiso('ver-boxes');
    }

    public function ver(User $user, Box $box): bool
    {
        return $user->isVeterinario() && $user->tienePermiso('ver-boxes');
    }

    public function crear(User $user): bool
    {
        return $user->isVeterinario() && $user->tienePermiso('crear-boxes');
    }

    public function editar(User $user, Box $box): bool
    {
        return $user->isVeterinario() && $user->tienePermiso('editar-boxes');
    }

    public function eliminar(User $user, Box $box): bool
    {
        return $user->isVeterinario() && $user->tienePermiso('eliminar-boxes');
    }
}

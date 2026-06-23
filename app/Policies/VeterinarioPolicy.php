<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Veterinario;
use Illuminate\Auth\Access\Response;

class VeterinarioPolicy
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
        return true;
    }

    public function ver(User $user, Veterinario $veterinario): bool
    {
        return true;
    }

    public function crear(User $user): bool
    {
        return $user->isAdmin() && $user->tienePermiso('crear-veterinarios');
    }

    public function editar(User $user, Veterinario $veterinario): bool
    {
        return $user->isAdmin() && $user->tienePermiso('editar-veterinarios');
    }

    public function eliminar(User $user, Veterinario $veterinario): bool
    {
        return $user->isAdmin() && $user->tienePermiso('eliminar-veterinarios');
    }
}

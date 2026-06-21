<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Raza;
use Illuminate\Auth\Access\Response;

class RazaPolicy
{

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

    /**
     * Determina si el usuario puede ver los detalles de una mascota específica.
     */
    public function ver(User $user, Raza $raza): bool
    {

        return true;
    }
    public function crear(User $user): bool
    {
        return $user->isVeterinario() && $user->tienePermiso('crear-especies');
    }


    public function editar(User $user, Raza $raza): bool
    {
        if ($user->isVeterinario()) {
            return $user->tienePermiso('editar-razas');
        }

        return false;
    }

    public function eliminar(User $user, Raza $raza): bool
    {
        if ($user->isVeterinario()) {
            return $user->tienePermiso('eliminar-razas');
        }

        return false;
    }
}

<?php

namespace App\Policies;

use App\Models\User;
use App\Models\CitaCargo;

class CitaCargoPolicy
{
    public function before(User $user, string $ability)
    {
        if ($user->isAdmin()) {
            return true;
        }
    }

    public function crear(User $user): bool
    {
        return $user->isVeterinario() && $user->tienePermiso('gestionar-cargos-sucursal');
    }

    public function actualizar(User $user, CitaCargo $cargo): bool
    {
        return $user->isVeterinario() && $user->tienePermiso('gestionar-cargos-sucursal');
    }

    public function eliminar(User $user, CitaCargo $cargo): bool
    {
        return $user->isVeterinario() && $user->tienePermiso('gestionar-cargos-sucursal');
    }
}

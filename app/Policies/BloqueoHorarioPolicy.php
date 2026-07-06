<?php

namespace App\Policies;

use App\Models\User;

class BloqueoHorarioPolicy
{
    public function before(User $user, string $ability)
    {
        if ($user->isAdmin()) {
            return true;
        }
    }

    public function crear(User $user)
    {
        return false;
    }

    public function eliminar(User $user, BloqueoHorario $bloqueo)
    {
        return false;
    }
}

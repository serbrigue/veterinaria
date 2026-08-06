<?php

namespace App\Policies;

use App\Models\User;

/*
|--------------------------------------------------------------------------
| Bloqueo Horario Policy
|--------------------------------------------------------------------------
| Administrador: Acceso total automático para crear y eliminar bloqueos horarios.
| Otros roles (Veterinario, Secretaria, Cliente): Tienen denegadas por defecto todas las acciones de esta política.
*/
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

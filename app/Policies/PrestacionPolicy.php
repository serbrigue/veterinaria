<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Prestacion;
use Illuminate\Auth\Access\Response;

class PrestacionPolicy
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

    public function ver(User $user, Prestacion $prestacion): bool
    {
        return true;
    }

    public function crear(User $user): bool
    {
        return $user->isAdmin();
    }

    public function editar(User $user, Prestacion $prestacion): bool
    {
        return $user->isAdmin();
    }

    public function eliminar(User $user, Prestacion $prestacion): bool
    {
        if ($user->isAdmin()) {
            return true;
        }
        return false;
    }
}

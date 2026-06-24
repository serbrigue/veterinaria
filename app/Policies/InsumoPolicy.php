<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Insumo;
use Illuminate\Auth\Access\Response;

class InsumoPolicy
{

    public function before(User $user, string $ability)
    {
        if ($user->isAdmin()) {
            return true;
        }
    }

    public function verTodas(User $user): bool
    {
        return $user->isVeterinario() && $user->tienePermiso('ver-insumos');
    }

    public function ver(User $user, Insumo $insumo): bool
    {
        if ($user->isVeterinario() && $user->tienePermiso('ver-insumos')) {
            return true;
        }
    }

    public function crear(User $user): bool
    {
        return $user->isAdmin();
    }

    public function editar(User $user, Insumo $insumo): bool
    {
        return $user->isAdmin();
    }

    public function eliminar(User $user, Insumo $insumo): bool
    {
        if ($user->isAdmin()) {
            return true;
        }
        return false;
    }
}

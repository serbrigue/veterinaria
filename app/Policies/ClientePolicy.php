<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\Response;

class ClientePolicy
{

    public function before(User $user, string $ability)
    {
        if ($user->isAdmin()) {
            return true;
        }
    }

    public function verTodas(User $user): bool
    {
        return $user->isVeterinario() && $user->tienePermiso('ver-clientes');
    }

    public function ver(User $user, Cliente $cliente): bool
    {
        if ($user->isVeterinario() && $user->tienePermiso('ver-clientes')) {
            return true;
        }

        // Permitir si es el propio perfil del cliente
        return $user->cliente && $user->cliente->id === $cliente->id;
    }

    public function crear(User $user): bool
    {
        return false;
    }

    public function editar(User $user, Cliente $cliente): bool
    {
        if ($user->isVeterinario() && $user->tienePermiso('editar-clientes')) {
            return true;
        }

        // Permitir editar su propio perfil
        return $user->cliente && $user->cliente->id === $cliente->id;
    }

    public function eliminar(User $user, Cliente $cliente): bool
    {
        // Nadie puede eliminar clientes
        return false;
    }
}

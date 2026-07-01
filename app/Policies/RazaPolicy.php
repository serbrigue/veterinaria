<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Raza;


class RazaPolicy
{

    #El filtro before se ejecuta antes de cualquier otro método de la Policy.
    #Si el usuario es administrador supremo, le otorgamos acceso total automático (bypass).
    public function before(User $user, string $ability)
    {
        if ($user->isAdmin()) {
            return true;
        }
    }

    #Verifica si el usuario tiene permiso para ver todas las razas
    public function verTodas(User $user): bool
    {
        #Cualquier usuario autenticado puede ver todas las razas
        return true;
    }

    #Verifica si el usuario tiene permiso para ver los detalles de una raza específica
    public function ver(User $user, Raza $raza): bool
    {
        #Cualquier usuario autenticado puede ver los detalles de una raza específica
        return true;
    }

    #Verifica si el usuario tiene permiso para crear una raza
    public function crear(User $user): bool
    {
        #Solo veterinarios y administradores pueden crear razas
        return $user->isVeterinario() && $user->tienePermiso('crear-razas');
    }

    #Verifica si el usuario tiene permiso para editar una raza específica
    public function editar(User $user, Raza $raza): bool
    {
        #Solo veterinarios pueden editar razas
        if ($user->isVeterinario()) {
            return $user->tienePermiso('editar-razas');
        }

        return false;
    }

    #Verifica si el usuario tiene permiso para eliminar una raza específica
    public function eliminar(User $user, Raza $raza): bool
    {
        #Solo veterinarios pueden eliminar razas
        if ($user->isVeterinario()) {
            return $user->tienePermiso('eliminar-razas');
        }

        return false;
    }
}

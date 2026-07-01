<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Veterinario;
use Illuminate\Auth\Access\Response;

class VeterinarioPolicy
{
    #El filtro before se ejecuta antes de cualquier otro método de la Policy.
    #Si el usuario es administrador supremo, le otorgamos acceso total automático (bypass).
    public function before(User $user, string $ability)
    {
        if ($user->isAdmin()) {
            return true;
        }
    }

    #Verifica si el usuario tiene permiso para ver todos los veterinarios
    public function verTodas(User $user): bool
    {
        #Cualquier usuario autenticado puede ver todos los veterinarios
        return true;
    }

    #Verifica si el usuario tiene permiso para ver los detalles de un veterinario específico
    public function ver(User $user, Veterinario $veterinario): bool
    {
        #Cualquier usuario autenticado puede ver los detalles de un veterinario
        return true;
    }

    #Verifica si el usuario tiene permiso para crear un veterinario
    public function crear(User $user): bool
    {
        #Solo administradores pueden crear veterinarios
        return $user->isAdmin() && $user->tienePermiso('crear-veterinarios');
    }

    #Verifica si el usuario tiene permiso para editar un veterinario específico
    public function editar(User $user, Veterinario $veterinario): bool
    {
        #Solo administradores pueden editar veterinarios
        return $user->isAdmin() && $user->tienePermiso('editar-veterinarios');
    }

    #Verifica si el usuario tiene permiso para eliminar un veterinario específico
    public function eliminar(User $user, Veterinario $veterinario): bool
    {
        #Solo administradores pueden eliminar veterinarios
        return $user->isAdmin() && $user->tienePermiso('eliminar-veterinarios');
    }
}

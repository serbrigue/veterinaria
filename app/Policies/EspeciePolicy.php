<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Especie;

class EspeciePolicy
{

    #El filtro before se ejecuta antes de cualquier otro método de la Policy.
    #Si el usuario es administrador supremo, le otorgamos acceso total automático (bypass).
    public function before(User $user, string $ability)
    {
        if ($user->isAdmin()) {
            return true;
        }
    }

    #Verifica si el usuario tiene permiso para ver todas las especies
    public function verTodas(User $user): bool
    {
        #Cualquier usuario autenticado puede ver las especies
        return true;
    }

    #Verifica si el usuario tiene permiso para ver los detalles de una especie
    public function ver(User $user, Especie $especie): bool
    {
        return true;
    }

    #Verifica si el usuario tiene permiso para crear una especie
    public function crear(User $user): bool
    {
        return $user->isVeterinario() && $user->tienePermiso('crear-especies');
    }

    #Verifica si el usuario tiene permiso para editar una especie
    public function editar(User $user, Especie $especie): bool
    {
        #Si es veterinario
        if ($user->isVeterinario()) {

            #Requiere el permiso de edición de su sucursal
            return $user->tienePermiso('editar-especies');
        }

        return false;
    }

    #Verifica si el usuario tiene permiso para eliminar una especie
    public function eliminar(User $user, Especie $especie): bool
    { #Si es veterinario
        if ($user->isVeterinario()) {
            #Requiere el permiso de edición de su sucursal
            return $user->tienePermiso('eliminar-especies');
        }

        return false;
    }
}

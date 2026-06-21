<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Especie;
use Illuminate\Auth\Access\Response;

class EspeciePolicy
{
    /**
     * El filtro before se ejecuta antes de cualquier otro método de la Policy.
     * Si el usuario es administrador supremo, le otorgamos acceso total automático (bypass).
     */
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
    public function ver(User $user, Especie $especie): bool
    {

        return true;
    }
    public function crear(User $user): bool
    {
        return $user->isVeterinario() && $user->tienePermiso('crear-especies');
    }

    /**
     * Determina si el usuario puede actualizar una mascota específica.
     */
    public function editar(User $user, Especie $especie): bool
    {
        if ($user->isVeterinario()) {
            return $user->tienePermiso('editar-especies');
        }

        return false;
    }

    /**
     * Determina si el usuario puede eliminar una mascota específica.
     */

    public function eliminar(User $user, Especie $especie): bool
    {
        if ($user->isVeterinario()) {
            return $user->tienePermiso('eliminar-especies');
        }

        return false;
    }
}

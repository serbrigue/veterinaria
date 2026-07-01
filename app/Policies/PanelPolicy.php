<?php

namespace App\Policies;

use App\Models\User;

class PanelPolicy
{
    #El filtro before se ejecuta antes de cualquier otro método de la Policy.
    #Si el usuario es administrador supremo, le otorgamos acceso total automático (bypass).
    public function before(User $user, string $ability)
    {
        if ($user->isAdmin()) {
            return true;
        }
    }

    #Verifica si el usuario tiene permiso para ver el panel
    public function ver(User $user)
    {
        #Solo los administradores pasan el método before()
        return false;
    }
}

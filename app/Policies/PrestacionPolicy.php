<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Prestacion;
use Illuminate\Auth\Access\Response;

class PrestacionPolicy
{

    #El filtro before se ejecuta antes de cualquier otro método de la Policy.
    #Si el usuario es administrador supremo, le otorgamos acceso total automático (bypass).
    public function before(User $user, string $ability)
    {
        if ($user->isAdmin()) {
            return true;
        }
    }

    #Verifica si el usuario tiene permiso para ver todas las prestaciones
    public function verTodas(User $user): bool
    {
        #Cualquier usuario autenticado puede ver todas las prestaciones
        return true;
    }

    #Verifica si el usuario tiene permiso para ver una prestacion específica
    public function ver(User $user, Prestacion $prestacion): bool
    {
        #Cualquier usuario autenticado puede ver una prestacion específica
        return true;
    }

    #Verifica si el usuario tiene permiso para crear una prestacion
    public function crear(User $user): bool
    {
        #Solo administradores pueden crear prestaciones
        return $user->isAdmin();
    }

    #Verifica si el usuario tiene permiso para editar una prestacion específica
    public function editar(User $user, Prestacion $prestacion): bool
    {
        #Solo administradores pueden editar prestaciones
        return $user->isAdmin();
    }

    #Verifica si el usuario tiene permiso para eliminar una prestacion específica
    public function eliminar(User $user, Prestacion $prestacion): bool
    {
        #Solo administradores pueden eliminar prestaciones
        if ($user->isAdmin()) {
            return true;
        }
        return false;
    }
}

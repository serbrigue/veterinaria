<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Insumo;
use Illuminate\Auth\Access\Response;

class InsumoPolicy
{

    #El filtro before se ejecuta antes de cualquier otro método de la Policy.
    #Si el usuario es administrador supremo, le otorgamos acceso total automático (bypass).
    public function before(User $user, string $ability)
    {
        if ($user->isAdmin()) {
            return true;
        }
    }

    #Verifica si el usuario tiene permiso para ver todos los insumos
    public function verTodas(User $user): bool
    { #Solo veterinarios y administradores pueden ver insumos
        return $user->isVeterinario() && $user->tienePermiso('ver-insumos');
    }

    #Verifica si el usuario tiene permiso para ver un insumo específico

    public function ver(User $user, Insumo $insumo): bool

    {   #Solo veterinarios y administradores pueden ver insumos

        return $user->isVeterinario() && $user->tienePermiso('ver-insumos');
    }

    #Verifica si el usuario tiene permiso para crear un insumo

    public function crear(User $user): bool
    {
        #Solo administradores pueden crear insumos
        return $user->isAdmin();
    }

    #Verifica si el usuario tiene permiso para editar un insumo

    public function editar(User $user, Insumo $insumo): bool
    {
        #Solo administradores pueden editar insumos
        return $user->isAdmin();
    }

    #Verifica si el usuario tiene permiso para eliminar un 

    public function eliminar(User $user, Insumo $insumo): bool
    {
        #Solo administradores pueden eliminar insumos
        if ($user->isAdmin()) {
            return true;
        }
        return false;
    }
}

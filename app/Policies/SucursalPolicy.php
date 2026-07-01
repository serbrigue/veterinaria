<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Sucursal;
use Illuminate\Auth\Access\Response;

class SucursalPolicy
{
    #El filtro before se ejecuta antes de cualquier otro método de la Policy.
    #Si el usuario es administrador supremo, le otorgamos acceso total automático (bypass).
    public function before(User $user, string $ability)
    {
        if ($user->isAdmin()) {
            return true;
        }
    }

    #Verifica si el usuario tiene permiso para ver todas las sucursales
    public function verTodas(User $user): bool
    {
        #Solo veterinarios y administradores pueden ver sucursales
        return $user->isVeterinario() && $user->tienePermiso('ver-sucursales');
    }

    #Verifica si el usuario tiene permiso para ver los detalles de una sucursal específica
    public function ver(User $user, Sucursal $sucursal): bool
    {
        #Solo veterinarios y administradores pueden ver sucursales
        return $user->isVeterinario() && $user->tienePermiso('ver-sucursales');
    }

    #Verifica si el usuario tiene permiso para crear una sucursal
    public function crear(User $user): bool
    {
        #Solo veterinarios y administradores pueden crear sucursales
        return $user->isVeterinario() && $user->tienePermiso('crear-sucursales');
    }

    #Verifica si el usuario tiene permiso para editar una sucursal específica
    public function editar(User $user, Sucursal $sucursal): bool
    {
        #Solo veterinarios y administradores pueden editar sucursales
        return $user->isVeterinario() && $user->tienePermiso('editar-sucursales');
    }

    #Verifica si el usuario tiene permiso para eliminar una sucursal específica
    public function eliminar(User $user, Sucursal $sucursal): bool
    {
        #Solo veterinarios y administradores pueden eliminar sucursales
        return $user->isVeterinario() && $user->tienePermiso('eliminar-sucursales');
    }
}

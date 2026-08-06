<?php

namespace App\Policies;

use App\Models\CitaCargo;
use App\Models\User;

/*
|--------------------------------------------------------------------------
| Cita Cargo Policy
|--------------------------------------------------------------------------
| Administrador: Acceso total automático a todas las acciones.
| Veterinario: Puede crear, editar y eliminar cargos de cita solo si cuenta con el permiso 'gestionar-cargos-sucursal'.
| Otros roles (Cliente, Secretaria): No tienen permisos para gestionar los cargos de citas.
*/
class CitaCargoPolicy
{
    // El filtro before se ejecuta antes de cualquier otro método de la Policy.
    // Si el usuario es administrador supremo, le otorgamos acceso total automático (bypass).
    public function before(User $user, string $ability)
    {
        if ($user->isAdmin()) {
            return true;
        }
    }

    // Verifica si el usuario tiene permiso para crear un cargo
    public function crear(User $user): bool
    {
        return $user->isVeterinario() && $user->tienePermiso('gestionar-cargos-sucursal');
    }

    // Verifica si el usuario tiene permiso para editar un cargo
    public function actualizar(User $user, CitaCargo $cargo): bool
    {
        return $user->isVeterinario() && $user->tienePermiso('gestionar-cargos-sucursal');
    }

    // Verifica si el usuario tiene permiso para eliminar un cargo
    public function eliminar(User $user, CitaCargo $cargo): bool
    {
        return $user->isVeterinario() && $user->tienePermiso('gestionar-cargos-sucursal');
    }
}

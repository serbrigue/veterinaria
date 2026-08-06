<?php

namespace App\Policies;

use App\Models\User;

/*
|--------------------------------------------------------------------------
| Pagos Veterinarios Policy
|--------------------------------------------------------------------------
| Administrador: Acceso total automático a todas las acciones de pagos veterinarios.
| Otros roles (Veterinario, Cliente, Secretaria): Tienen denegadas por defecto todas las acciones de visualización y gestión en esta política.
*/
class PagosVeterinariosPolicy
{
    // El filtro before se ejecuta antes de cualquier otro método de la Policy.
    // Si el usuario es administrador supremo, le otorgamos acceso total automático (bypass).
    public function before(User $user, string $ability)
    {
        if ($user->isAdmin()) {
            return true;
        }
    }

    public function verTodas(User $user): bool
    {
        return false;
    }

    // Verifica si el usuario tiene permiso para ver los detalles de una mascota específica
    public function ver(User $user, User $veterinario): bool
    {

        return false;
    }

    // Verifica si el usuario tiene permiso para crear una mascota
    public function crear(User $user): bool
    {

        return false;
    }

    // Verifica si el usuario tiene permiso para editar una mascota específica
    public function editar(User $user, User $veterinario): bool
    {

        return false;
    }

    // Verifica si el usuario tiene permiso para eliminar una mascota específica
    public function eliminar(User $user, User $veterinario): bool
    {

        return false;
    }
}

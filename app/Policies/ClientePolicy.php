<?php

namespace App\Policies;

use App\Models\Cliente;
use App\Models\User;

/*
|--------------------------------------------------------------------------
| Cliente Policy
|--------------------------------------------------------------------------
| Administrador: Tiene acceso a todas las acciones (ver, crear, editar, eliminar).
| Secretaria: Tiene acceso completo a la gestión de clientes (ver, crear, editar, eliminar).
| Veterinario: Solo puede ver el listado y los detalles de los clientes. No puede crearlos, editarlos ni eliminarlos.
| Cliente: Solo puede editar sus propios detalles (si su user_id coincide con el cliente). No puede ver la lista general, crear ni eliminar.
*/
class ClientePolicy
{
    // Verifica si el usuario tiene permiso para ver todos los clientes
    public function verTodas(User $user)
    {
        return $user->isAdmin()
            || $user->isVeterinario()
            || $user->rol?->nombre_interno === 'secretaria';
    }

    // Verifica si el usuario tiene permiso para ver los detalles de un cliente
    public function ver(User $user, Cliente $cliente)
    {
        return $user->isAdmin()
            || $user->isVeterinario()
            || $user->rol?->nombre_interno === 'secretaria';
    }

    // Verifica si el usuario tiene permiso para crear un cliente
    public function crear(User $user)
    {
        return $user->isAdmin() || $user->isSecretaria();
    }

    // Verifica si el usuario tiene permiso para editar un cliente
    public function editar(User $user, Cliente $cliente)
    {
        // Solo administradores, secretarias y el propio cliente pueden editar los detalles
        return $user->isAdmin()
            || $user->rol?->nombre_interno === 'secretaria'
            || $user->id === $cliente->user_id;
    }

    // Verifica si el usuario tiene permiso para eliminar un cliente
    public function eliminar(User $user, Cliente $cliente)
    {
        // Solo administradores y secretarias pueden eliminar clientes
        return $user->isAdmin()
            || $user->rol?->nombre_interno === 'secretaria';
    }
}

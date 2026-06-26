<?php

namespace App\Policies;

use App\Models\Cliente;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ClientePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function verTodas(User $user)
    {
        return $user->isAdmin() || $user->isVeterinario();
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Cliente  $cliente
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function ver(User $user, Cliente $cliente)
    {
        // Solo administradores y veterinarios pueden ver detalles de clientes
        // (Si se desea en el futuro que un cliente vea su propio detalle: || $user->id === $cliente->user_id)
        return $user->isAdmin() || $user->isVeterinario();
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function create(User $user)
    {
        return true;
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Cliente  $cliente
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(User $user, Cliente $cliente)
    {
        return $user->isAdmin() || $user->isVeterinario() || $user->id === $cliente->user_id;
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Cliente  $cliente
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(User $user, Cliente $cliente)
    {
        return $user->isAdmin() || $user->isVeterinario();
    }
}

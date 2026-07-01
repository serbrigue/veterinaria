<?php

namespace App\Policies;

use App\Models\Cliente;
use App\Models\User;

class ClientePolicy
{

    #Verifica si el usuario tiene permiso para ver todos los clientes
    public function verTodas(User $user)
    {
        return $user->isAdmin() || $user->isVeterinario();
    }

    #Verifica si el usuario tiene permiso para ver los detalles de un cliente
    public function ver(User $user, Cliente $cliente)
    { #Solo administradores y veterinarios pueden ver detalles de clientes
        return $user->isAdmin() || $user->isVeterinario();
    }

    #Verifica si el usuario tiene permiso para crear un cliente
    public function create(User $user)
    {
        return true;
    }

    #Verifica si el usuario tiene permiso para editar un cliente
    public function update(User $user, Cliente $cliente)
    {
        #Solo administradores y veterinarios pueden editar detalles de clientes
        return $user->isAdmin() || $user->isVeterinario() || $user->id === $cliente->user_id;
    }

    #Verifica si el usuario tiene permiso para eliminar un cliente
    public function delete(User $user, Cliente $cliente)

    {
        #Solo administradores y veterinarios pueden eliminar clientes
        return $user->isAdmin() || $user->isVeterinario();
    }
}

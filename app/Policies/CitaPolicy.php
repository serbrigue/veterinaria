<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Cita;
use Illuminate\Auth\Access\Response;

class CitaPolicy
{

    #El filtro before se ejecuta antes de cualquier otro método de la Policy.
    #Si el usuario es administrador supremo, le otorgamos acceso total automático (bypass).
    public function before(User $user, string $ability)
    {
        if ($user->isAdmin()) {
            return true;
        }
    }

    #Verifica si el usuario tiene permiso para ver todas las citas
    public function verTodas(User $user): bool
    {
        #Un cliente necesita el permiso de ver sus propias citas
        if ($user->isCliente() && $user->tienePermiso('ver-mis-citas')) {
            return true;
        }

        #Un veterinario necesita el permiso de ver las de su sucursal
        if ($user->isVeterinario() && $user->tienePermiso('ver-citas-sucursal')) {
            return true;
        }

        return false;
    }

    #Verifica si el usuario tiene permiso para ver los detalles de una cita específica
    public function ver(User $user, Cita $cita): bool
    {#Si es cliente, solo puede ver la cita si es el propietario (dueño) de la mascota asociada
        if ($user->isCliente()) {
            return $user->tienePermiso('ver-mis-citas') && $cita->mascota?->cliente_id == $user->cliente?->id;
        }

        #Si es veterinario, de momento requiere el permiso general de sucursal
        if ($user->isVeterinario()) {
            return $user->tienePermiso('ver-citas-sucursal');
        }

        return false;
    }


    #Verifica si el usuario tiene permiso para crear una cita
    public function crear(User $user): bool
    {
        return $user->isCliente() && $user->tienePermiso('agendar-cita');
    }

    #Verifica si el usuario tiene permiso para editar una cita
    public function editar(User $user, Cita $cita): bool
    {#Si es cliente, requiere el permiso de edición y ser el propietario de la mascota asociada
        if ($user->isCliente()) {
            return $user->tienePermiso('editar-mis-citas') && $cita->mascota?->cliente_id === $user->cliente?->id;
        }

        #Si es veterinario, requiere el permiso de edición de su sucursal
        if ($user->isVeterinario()) {
            return $user->tienePermiso('editar-citas-sucursal');
        }

        return false;
    }

    #Verifica si el usuario tiene permiso para cancelar una cita
    public function cancelar(User $user, Cita $cita): bool
    {#No permitir cancelar citas ya finalizadas o canceladas
        if (in_array($cita->estado, ['completada', 'cancelada'])) {
            return false;
        }

        #Si es cliente
        if ($user->isCliente()) {
            #Solo el dueño puede cancelar la cita
            return $user->tienePermiso('eliminar-mis-citas')
                && $cita->mascota?->cliente_id === $user->cliente?->id;
        }

        #Si es veterinario
        if ($user->isVeterinario()) {
            #Requiere el permiso de edición de su sucursal
            return $user->tienePermiso('editar-citas-sucursal');
        }

        return false;
    }
}

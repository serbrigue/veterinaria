<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Cita;
use Illuminate\Auth\Access\Response;

class CitaPolicy
{

    public function before(User $user, string $ability)
    {
        if ($user->isAdmin()) {
            return true;
        }
    }

    /**
     * Determina si el usuario puede ver el listado de citas.
     */
    public function verTodas(User $user): bool
    {
        // Un cliente necesita el permiso de ver sus propias citas
        if ($user->isCliente() && $user->tienePermiso('ver-mis-citas')) {
            return true;
        }

        // Un veterinario necesita el permiso de ver las de su sucursal
        if ($user->isVeterinario() && $user->tienePermiso('ver-citas-sucursal')) {
            return true;
        }

        return false;
    }

    /**
     * Determina si el usuario puede ver los detalles de una cita específica.
     */
    public function ver(User $user, Cita $cita): bool
    {
        // Si es cliente, solo puede ver la cita si es el propietario (dueño) de la mascota asociada
        if ($user->isCliente()) {
            return $user->tienePermiso('ver-mis-citas') && $cita->mascota?->cliente_id == $user->cliente?->id;
        }

        // Si es veterinario, de momento requiere el permiso general de sucursal
        if ($user->isVeterinario()) {
            return $user->tienePermiso('ver-citas-sucursal');
        }

        return false;
    }

    /**
     * Determina si el usuario puede registrar nuevas citas.
     */
    public function crear(User $user): bool
    {
        return $user->isCliente() && $user->tienePermiso('agendar-cita');
    }

    /**
     * Determina si el usuario puede actualizar una cita específica.
     */
    public function editar(User $user, Cita $cita): bool
    {
        // Si es cliente, requiere el permiso de edición y ser el propietario de la mascota asociada
        if ($user->isCliente()) {
            return $user->tienePermiso('editar-mis-citas') && $cita->mascota?->cliente_id === $user->cliente?->id;
        }

        // Si es veterinario, requiere el permiso de edición de su sucursal
        if ($user->isVeterinario()) {
            return $user->tienePermiso('editar-citas-sucursal');
        }

        return false;
    }

    /**
     * Determina si el usuario puede cancelar una cita específica.
     * Un cliente solo puede cancelar sus propias citas que aún estén pendientes.
     * Los veterinarios pueden cancelar citas de su sucursal.
     */
    public function cancelar(User $user, Cita $cita): bool
    {
        // No permitir cancelar citas ya finalizadas o canceladas
        if (in_array($cita->estado, ['completada', 'cancelada'])) {
            return false;
        }

        if ($user->isCliente()) {
            return $user->tienePermiso('eliminar-mis-citas')
                && $cita->mascota?->cliente_id === $user->cliente?->id;
        }

        if ($user->isVeterinario()) {
            return $user->tienePermiso('editar-citas-sucursal');
        }

        return false;
    }
}

<?php

namespace App\Policies;

use App\Models\Cita;
use App\Models\User;

class CitaPolicy
{
    // El filtro before se ejecuta antes de cualquier otro método de la Policy.
    // Si el usuario es administrador supremo, le otorgamos acceso total automático (bypass).
    public function before(User $user, string $ability)
    {
        if ($user->isAdmin()) {
            return true;
        }
    }

    // Verifica si el usuario tiene permiso para ver todas las citas
    public function verTodas(User $user): bool
    {
        // Un cliente necesita el permiso de ver sus propias citas
        if ($user->isCliente() && $user->tienePermiso('ver-mis-citas')) {
            return true;
        }

        // Un veterinario solo maneja sus citas
        if ($user->isVeterinario() && $user->tienePermiso('ver-mis-citas')) {
            return true;
        }

        // Una secretaria necesita el permiso de ver las de su sucursal
        if ($user->rol?->nombre_interno === 'secretaria' && $user->tienePermiso('ver-citas-sucursal')) {
            return true;
        }

        return false;
    }

    // Verifica si el usuario tiene permiso para ver los detalles de una cita específica
    public function ver(User $user, Cita $cita): bool
    {
        // Si es cliente, solo puede ver la cita si es el propietario (dueño) de la mascota asociada
        if ($user->isCliente()) {
            return $user->tienePermiso('ver-mis-citas') && $cita->mascota?->cliente_id == $user->cliente?->id;
        }

        // Si es veterinario, solo puede ver sus propias citas
        if ($user->isVeterinario()) {
            return $cita->veterinario_id === $user->veterinario?->id;
        }

        // Si es secretaria, puede ver las citas de los veterinarios de su sucursal
        if ($user->rol?->nombre_interno === 'secretaria') {
            return $user->tienePermiso('ver-citas-sucursal') 
                && $cita->veterinario?->sucursal_id === $user->secretaria?->sucursal_id;
        }

        return false;
    }

    // Verifica si el usuario tiene permiso para crear una cita
    public function crear(User $user): bool
    {
        if ($user->isCliente()) {
            return $user->tienePermiso('agendar-cita');
        }

        if ($user->rol?->nombre_interno === 'secretaria') {
            return $user->tienePermiso('gestionar-citas-sucursal');
        }

        return false;
    }

    // Verifica si el usuario tiene permiso para reprogramar/actualizar datos generales de una cita
    public function actualizar(User $user, Cita $cita): bool
    {
        if ($user->rol?->nombre_interno === 'secretaria') {
            return $user->tienePermiso('editar-citas-sucursal') 
                && $cita->veterinario?->sucursal_id === $user->secretaria?->sucursal_id;
        }

        return false;
    }

    // Verifica si el usuario tiene permiso para editar las notas clínicas de una cita
    public function editarNotas(User $user, Cita $cita): bool
    {
        if ($user->isVeterinario()) {
            return $cita->veterinario_id === $user->veterinario?->id;
        }

        if ($user->rol?->nombre_interno === 'secretaria') {
            return $user->tienePermiso('editar-citas-sucursal') 
                && $cita->veterinario?->sucursal_id === $user->secretaria?->sucursal_id;
        }

        return false;
    }

    // Verifica si el usuario tiene permiso para editar el estado de una cita
    public function editarEstado(User $user, Cita $cita): bool
    {
        if ($user->isVeterinario()) {
            return $cita->veterinario_id === $user->veterinario?->id;
        }

        if ($user->rol?->nombre_interno === 'secretaria') {
            return $user->tienePermiso('editar-citas-sucursal') 
                && $cita->veterinario?->sucursal_id === $user->secretaria?->sucursal_id;
        }

        return false;
    }

    // Verifica si el usuario tiene permiso para cancelar una cita
    public function cancelar(User $user, Cita $cita): bool
    {
        // No permitir cancelar citas ya finalizadas o canceladas
        if (in_array($cita->estado, ['completada', 'cancelada'])) {
            return false;
        }

        // Si es cliente
        if ($user->isCliente()) {
            return $user->tienePermiso('eliminar-mis-citas')
                && $cita->mascota?->cliente_id === $user->cliente?->id;
        }

        // Si es secretaria
        if ($user->rol?->nombre_interno === 'secretaria') {
            return $user->tienePermiso('editar-citas-sucursal') 
                && $cita->veterinario?->sucursal_id === $user->secretaria?->sucursal_id;
        }

        return false;
    }
}

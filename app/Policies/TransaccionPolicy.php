<?php

namespace App\Policies;

use App\Models\Transaccion;
use App\Models\User;

/*
|--------------------------------------------------------------------------
| Transaccion Policy
|--------------------------------------------------------------------------
| Administrador: Acceso total automático a todas las acciones.
| Cliente: Solo puede realizar el pago de sus propias transacciones, siempre y cuando cuente con el permiso 'pagar-transacciones'.
| Otros roles (Veterinario, Secretaria): Tienen denegadas por defecto todas las acciones (ver, crear, editar, eliminar y pagar transacciones).
*/
class TransaccionPolicy
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

    public function ver(User $user, Transaccion $transaccion): bool
    {
        return false;
    }

    public function crear(User $user): bool
    {
        return false;
    }

    public function editar(User $user, Transaccion $transaccion): bool
    {
        return false;
    }

    public function eliminar(User $user, Transaccion $transaccion): bool
    {
        return false;
    }

    // Verifica si el usuario tiene permiso para pagar la transacción
    public function pagar(User $user, Transaccion $transaccion): bool
    {
        // Verificar si el rol del usuario tiene el permiso global en base de datos
        if (! $user->tienePermiso('pagar-transacciones')) {
            return false;
        }

        // Un cliente solo puede pagar sus propias transacciones
        if ($user->rol && $user->rol->nombre_interno === 'cliente') {

            // Verificar si el cliente es el dueño de la transacción
            return $user->cliente && $user->cliente->id === $transaccion->cliente_id;
        }

        return false;
    }
}

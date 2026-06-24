<?php

namespace App\Policies;

use App\Models\Transaccion;
use App\Models\User;

class TransaccionPolicy
{
    /**
     * Los administradores pueden procesar cualquier transacción (por ejemplo, pagos físicos en caja).
     */
    public function before(User $user, string $ability)
    {
        if ($user->isAdmin()) {
            return true;
        }
    }

    /**
     * Determina si el usuario puede pagar la transacción.
     */
    public function pagar(User $user, Transaccion $transaccion)
    {
        // Verificar si el rol del usuario tiene el permiso global en base de datos
        if (!$user->tienePermiso('pagar-transacciones')) {
            return false;
        }

        // Un cliente solo puede pagar sus propias transacciones
        if ($user->rol && $user->rol->nombre_interno === 'cliente') {
            return $user->cliente && $user->cliente->id === $transaccion->cliente_id;
        }
        
        return false;
    }
}

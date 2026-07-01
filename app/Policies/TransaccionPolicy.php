<?php

namespace App\Policies;

use App\Models\Transaccion;
use App\Models\User;

class TransaccionPolicy
{
    #El filtro before se ejecuta antes de cualquier otro método de la Policy.
    #Si el usuario es administrador supremo, le otorgamos acceso total automático (bypass).
    public function before(User $user, string $ability)
    {
        if ($user->isAdmin()) {
            return true;
        }
    }

    #Verifica si el usuario tiene permiso para pagar la transacción
    public function pagar(User $user, Transaccion $transaccion): bool
    {
        #Verificar si el rol del usuario tiene el permiso global en base de datos
        if (!$user->tienePermiso('pagar-transacciones')) {
            return false;
        }

        # Un cliente solo puede pagar sus propias transacciones
        if ($user->rol && $user->rol->nombre_interno === 'cliente') {

            #Verificar si el cliente es el dueño de la transacción
            return $user->cliente && $user->cliente->id === $transaccion->cliente_id;
        }

        return false;
    }
}

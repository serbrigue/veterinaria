<?php

namespace App\Policies;

use App\Models\User;

/*
|--------------------------------------------------------------------------
| Import Policy
|--------------------------------------------------------------------------
| Administrador y Secretaria: Tienen acceso permitido para utilizar la función de importación de datos.
| Veterinario y Cliente: No tienen acceso a esta funcionalidad.
*/
class ImportPolicy
{
    /**
     * Determine if the given user can access the import feature.
     */
    public function importar(User $user): bool
    {
        return $user->isAdmin() || $user->isSecretaria();
    }
}

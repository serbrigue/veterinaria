<?php

namespace App\Policies;

use App\Models\User;

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

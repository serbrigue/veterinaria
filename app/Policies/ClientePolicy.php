<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\Response;

class ClientePolicy
{

    public function before(User $user, string $ability)
    {
        if ($user->isAdmin() || $user->isVeterinario()) {
            return true;
        }
    }

}

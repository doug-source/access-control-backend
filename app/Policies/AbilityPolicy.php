<?php

namespace App\Policies;

use App\Models\User;

final class AbilityPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->isSuperAdmin();
    }
}

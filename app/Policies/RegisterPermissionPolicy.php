<?php

namespace App\Policies;

use App\Models\RegisterPermission;
use App\Models\User;

class RegisterPermissionPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->isSuperAdmin();
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, RegisterPermission $registerPermission): bool
    {
        return $user->isSuperAdmin();
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->isSuperAdmin();
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, RegisterPermission $registerPermission): bool
    {
        return $user->isSuperAdmin();
    }
}

<?php

namespace App\Policies;

use App\Models\Ability;
use App\Models\User;
use Illuminate\Auth\Access\Response;

final class AbilityPolicy
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
    public function view(User $user, Ability $ability): bool
    {
        return $user->isSuperAdmin();
    }

    /**
     * Determine whether the user can create the model.
     */
    public function create(User $user): bool
    {
        return $user->isSuperAdmin();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Ability $ability)
    {
        return $user->isSuperAdmin();
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Ability $ability)
    {
        return $user->isSuperAdmin();
    }
}

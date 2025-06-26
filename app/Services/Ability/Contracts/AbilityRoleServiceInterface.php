<?php

declare(strict_types=1);

namespace App\Services\Ability\Contracts;

use App\Models\Role;
use Illuminate\Support\Collection;

interface AbilityRoleServiceInterface
{
    /**
     * Handle the role's abilities inclusion
     *
     * @param \Illuminate\Support\Collection<int, \App\Models\User> $usersFromRole
     * @param \Illuminate\Support\Collection<int, string> $namesToInclude
     */
    public function handleRoleAbilityInclusion(Collection $usersFromRole, Collection $namesToInclude): void;

    /**
     * Handle the role's abilities remotion
     *
     * @param \Illuminate\Support\Collection<int, \App\Models\User> $usersFromRole
     * @param \Illuminate\Support\Collection<int, string> $namesToRemove
     * @param \App\Models\Role $role
     */
    public function handleRoleAbilityRemotion(Collection $usersFromRole, Collection $namesToRemove, Role $role): void;
}

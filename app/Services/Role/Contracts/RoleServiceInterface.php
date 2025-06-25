<?php

declare(strict_types=1);

namespace App\Services\Role\Contracts;

use App\Models\User;
use App\Services\Ability\Contracts\AbilityServiceInterface;
use App\Services\Ability\Contracts\AbilityUserServiceInterface;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection as BaseCollection;

interface RoleServiceInterface
{
    /**
     * Update the User's roles removing and inserting role instances
     *
     * @param \App\Models\User $user
     * @param \Illuminate\Support\Collection<int, string> $namesToRemove
     * @param \Illuminate\Support\Collection<int, string> $namesToInclude
     */
    public function updateUserRoles(User $user, BaseCollection $namesToRemove, BaseCollection $namesToInclude): void;

    /**
     * Search by roles belong to (or not belong to) user
     */
    public function findReferenceRoles(User $user, bool $owner, int $page, int $group, ?string $name = NULL): LengthAwarePaginator;

    /**
     * Handle the user's role insertion dependencies
     *
     * @param \App\Models\User $user
     * @param \Illuminate\Support\Collection<int, \App\Models\Role> $rolesFromUser
     * @param \Illuminate\Support\Collection<int, string> $namesToInclude
     * @param \App\Services\Ability\Contracts\AbilityServiceInterface $abilityService
     * @param \App\Services\Ability\Contracts\AbilityUserServiceInterface $abilityUserService
     */
    public function handleUserRoleInsertion(
        User $user,
        BaseCollection $rolesFromUser,
        BaseCollection $namesToInclude,
        AbilityServiceInterface $abilityService,
        AbilityUserServiceInterface $abilityUserService
    ): void;
}

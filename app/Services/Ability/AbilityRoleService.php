<?php

declare(strict_types=1);

namespace App\Services\Ability;

use App\Models\Role;
use App\Services\Ability\Contracts\AbilityRoleServiceInterface;
use Illuminate\Support\Collection;

class AbilityRoleService implements AbilityRoleServiceInterface
{
    /**
     * Filter role users to be used in ability Remotion
     *
     * @param \Illuminate\Support\Collection<int, \App\Models\User> $usersFromRole
     * @param \App\Models\Role $role
     * @param string $name
     * @return \Illuminate\Support\Collection<int, \App\Models\User>
     */
    private function filterUsersFromRoleToAbilityRemotion(Collection $usersFromRole, Role $role, string $name): Collection
    {
        return $usersFromRole->reject(
            fn($user) => $user->roles->reject(
                fn($roleFromUser) => $roleFromUser->id === $role->id
            )->contains(
                fn($roleFromUser) => $roleFromUser->abilities->contains(
                    fn($abilityFromRole) => $abilityFromRole->name === $name
                )
            )
        );
    }

    /**
     * Handle the role's abilities
     *
     * @param \Illuminate\Support\Collection<int, \App\Models\User> $usersFromRole
     * @param bool $abilityStatus
     * @param \Illuminate\Support\Collection<int, string> $namesToInclude
     * @param null|\App\Models\Role $role
     */
    private function handleRoleAbilityDependencies(Collection $usersFromRole, Collection $namesToHandle, bool $abilityStatus, ?Role $role = NULL): void
    {
        $namesToHandle->each(
            function (string $name) use ($usersFromRole, $role, $abilityStatus) {
                if ($abilityStatus === FALSE && $role) {
                    $usersFromRole = $this->filterUsersFromRoleToAbilityRemotion(
                        usersFromRole: $usersFromRole,
                        role: $role,
                        name: $name
                    );
                }
                $usersFromRole->each(function ($user) use ($name, $abilityStatus) {
                    $abilityIds = $user->abilities->filter(
                        fn($ability) => (
                            $ability->name === $name && $ability->pivot->include === $abilityStatus
                        )
                    )->pluck('id');
                    if ($abilityIds->isNotEmpty()) {
                        $user->abilities()->detach($abilityIds->all());
                    }
                });
            }
        );
    }

    public function handleRoleAbilityInclusion(Collection $usersFromRole, Collection $namesToInclude): void
    {
        $this->handleRoleAbilityDependencies(
            usersFromRole: $usersFromRole,
            namesToHandle: $namesToInclude,
            abilityStatus: TRUE
        );
    }

    public function handleRoleAbilityRemotion(Collection $usersFromRole, Collection $namesToRemove, Role $role): void
    {
        $this->handleRoleAbilityDependencies(
            usersFromRole: $usersFromRole,
            namesToHandle: $namesToRemove,
            abilityStatus: FALSE,
            role: $role
        );
    }
}

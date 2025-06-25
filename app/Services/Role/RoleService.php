<?php

declare(strict_types=1);

namespace App\Services\Role;

use App\Models\Role;
use App\Services\Role\Contracts\RoleServiceInterface;
use App\Repositories\RoleRepository;
use Illuminate\Support\Collection as BaseCollection;
use App\Models\User;
use App\Repositories\UserRepository;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use App\Services\Ability\Contracts\AbilityServiceInterface;
use App\Services\Ability\Contracts\AbilityUserServiceInterface;

final class RoleService implements RoleServiceInterface
{
    public function __construct(
        private readonly RoleRepository $roleRepository,
        private readonly UserRepository $userRepository,
    ) {
        // ...
    }

    /**
     * Receive role names to remove from collection role
     *
     * @param \Illuminate\Database\Eloquent\Collection<int, \App\Models\Role> $roles
     * @param \Illuminate\Support\Collection<int, string> $namesToRemove
     * @return \Illuminate\Database\Eloquent\Collection<int, \App\Models\Role>
     */
    private function separate(EloquentCollection $roles, BaseCollection $namesToRemove): EloquentCollection
    {
        return $roles->reject(
            fn(Role $role) => $namesToRemove->contains(
                fn(string $roleName) => $roleName === $role->name
            )
        );
    }

    /**
     * Reject the roles not removed from collection as well as add the new roles to collection returned
     *
     * @param \Illuminate\Database\Eloquent\Collection<int, \App\Models\Role> $roles
     * @param \Illuminate\Support\Collection<int, string> $namesToRemove
     * @param \Illuminate\Support\Collection<int, string> $namesToInclude
     * @return \Illuminate\Database\Eloquent\Collection<int, \App\Models\Role>
     */
    private function combine(EloquentCollection $roles, BaseCollection $namesToRemove, BaseCollection $namesToInclude): EloquentCollection
    {
        return $this->separate(roles: $roles, namesToRemove: $namesToRemove)->concat(
            $this->roleRepository->findByNames($namesToInclude)->all()
        );
    }

    public function updateUserRoles(User $user, BaseCollection $namesToRemove, BaseCollection $namesToInclude): void
    {
        $user->roles()->sync(
            $this->combine(
                roles: $user->roles,
                namesToRemove: $namesToRemove,
                namesToInclude: $namesToInclude,
            )->pluck('id')->all()
        );
    }

    public function findReferenceRoles(User $user, bool $owner, int $page, int $group, ?string $name = NULL): LengthAwarePaginator
    {
        if ($owner) {
            return $this->userRepository->findRoles(
                user: $user,
                page: $page,
                group: $group,
                name: $name,
            );
        }
        return $this->roleRepository->findRoleListFiltered(
            page: $page,
            group: $group,
            exclude: $user->roles->pluck('id')->all(),
            name: $name,
        );
    }

    public function handleUserRoleInsertion(
        User $user,
        BaseCollection $rolesFromUser,
        BaseCollection $namesToInclude,
        AbilityServiceInterface $abilityService,
        AbilityUserServiceInterface $abilityUserService
    ): void {
        $abilitiesFromRolesFromUser = $abilityService->abilitiesFromRoles($rolesFromUser);
        $abilitiesIncludedFromUser = $abilityUserService->abilitiesIncludedFromUser($user);

        $abilitiesFromInclusion = $abilityService->abilitiesFromRoles(
            $this->roleRepository->findByNames($namesToInclude)
        );

        /** @var array<int, int> */
        $idsToDetach = $abilitiesFromInclusion->map(function ($abilities) use ($abilitiesFromRolesFromUser, $abilitiesIncludedFromUser) {
            return $abilities->reject(
                fn($ability) => $abilitiesFromRolesFromUser->some(
                    fn($abilityList) => $abilityList->contains(
                        fn($abilityFromList) => $abilityFromList->id === $ability->id
                    )
                )
            )->filter(
                fn($ability) => $abilitiesIncludedFromUser->contains(
                    fn($abilityIncluded) => $abilityIncluded->id === $ability->id
                )
            )->pluck('id')->all();
        })->flatten(1)->all();

        $user->abilities()->detach($idsToDetach);
    }
}

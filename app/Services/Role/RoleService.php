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
     * Filter the roles from the collection role using role's names
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

    /**
     * Handle the user's role insertion/remotion dependencies
     *
     * @param \Illuminate\Support\Collection<int, \Illuminate\Support\Collection<int, \App\Models\Ability>> $seedAbilitiesList
     * @param \Illuminate\Support\Collection<int, \Illuminate\Support\Collection<int, \App\Models\Ability>> $baseAbilitiesList
     * @param \Illuminate\Support\Collection<int, \App\Models\Ability> $filterAbilities
     */
    private function handleUserRoleDependencies(
        User $user,
        BaseCollection $seedAbilitiesList,
        BaseCollection $baseAbilitiesList,
        BaseCollection $filterAbilities,
    ) {
        $user->abilities()->detach(
            $seedAbilitiesList->map(function ($abilities) use ($baseAbilitiesList, $filterAbilities) {
                return $abilities->reject(
                    fn($ability) => $baseAbilitiesList->some(
                        fn($abilityList) => $abilityList->contains(
                            fn($abilityFromList) => $abilityFromList->id === $ability->id
                        )
                    )
                )->filter(
                    fn($ability) => $filterAbilities->contains(
                        fn($abilityIncluded) => $abilityIncluded->id === $ability->id
                    )
                )->pluck('id')->all();
            })->flatten(1)->all()
        );
    }

    public function handleUserRoleInsertion(
        User $user,
        BaseCollection $rolesFromUser,
        BaseCollection $namesToInclude,
        AbilityServiceInterface $abilityService,
        AbilityUserServiceInterface $abilityUserService
    ): void {
        $this->handleUserRoleDependencies(
            user: $user,
            seedAbilitiesList: $abilityService->abilitiesFromRoles(
                $this->roleRepository->findByNames($namesToInclude)
            ),
            baseAbilitiesList: $abilityService->abilitiesFromRoles($rolesFromUser),
            filterAbilities: $abilityUserService->abilitiesIncludedFromUser($user)
        );
    }

    public function handleUserRoleRemotion(
        User $user,
        BaseCollection $rolesFromUser,
        BaseCollection $namesToRemove,
        AbilityServiceInterface $abilityService,
        AbilityUserServiceInterface $abilityUserService
    ): void {
        $rolesFromUserFiltered = $rolesFromUser->reject(
            fn($role) => $namesToRemove->contains(
                fn($name) => $role->name === $name
            )
        );

        $this->handleUserRoleDependencies(
            user: $user,
            seedAbilitiesList: $abilityService->abilitiesFromRoles(
                $this->roleRepository->findByNames($namesToRemove)
            ),
            baseAbilitiesList: $abilityService->abilitiesFromRoles($rolesFromUserFiltered),
            filterAbilities: $abilityUserService->abilitiesRemovedFromUser($user)
        );
    }
}

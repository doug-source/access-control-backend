<?php

declare(strict_types=1);

namespace App\Services\Role;

use App\Models\Role;
use App\Services\Role\Contracts\RoleServiceInterface;
use App\Repositories\RoleRepository;
use Illuminate\Support\Collection as BaseCollection;
use Illuminate\Database\Eloquent\Collection;
use App\Models\User;
use App\Repositories\UserRepository;
use Illuminate\Pagination\LengthAwarePaginator;

final class RoleService implements RoleServiceInterface
{
    public function __construct(
        private readonly RoleRepository $roleRepository,
        private readonly UserRepository $userRepository,
    ) {
        // ...
    }

    public function combine(Collection $roles, BaseCollection $namesToRemove, BaseCollection $namesToInclude): Collection
    {
        return $this->separate(roles: $roles, namesToRemove: $namesToRemove)->concat(
            $this->roleRepository->findByNames($namesToInclude)->all()
        );
    }

    /**
     * Receive role names to remove from collection role
     *
     * @param \Illuminate\Database\Eloquent\Collection<int, \App\Models\Role> $roles
     * @param \Illuminate\Support\Collection<int, string> $namesToRemove
     * @return \Illuminate\Database\Eloquent\Collection<int, \App\Models\Role>
     */
    private function separate(Collection $roles, BaseCollection $namesToRemove): Collection
    {
        return $roles->reject(
            fn(Role $role) => $namesToRemove->contains(
                fn(string $roleName) => $roleName === $role->name
            )
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
}

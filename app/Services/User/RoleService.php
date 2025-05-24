<?php

declare(strict_types=1);

namespace App\Services\User;

use App\Models\Role;
use App\Services\User\Contracts\RoleServiceInterface;
use App\Repositories\RoleRepository;
use Illuminate\Support\Collection as BaseCollection;
use Illuminate\Database\Eloquent\Collection;

final class RoleService implements RoleServiceInterface
{
    public function __construct(private readonly RoleRepository $roleRepository)
    {
        // ...
    }

    public function combine(Collection $roles, BaseCollection $namesToRemove, BaseCollection $namesToInclude): Collection
    {
        return $roles->reject(
            fn(Role $role) => $namesToRemove->contains(
                fn(string $roleName) => $roleName === $role->name
            )
        )->concat(
            $this->roleRepository->findByNames($namesToInclude)->all()
        );
    }
}

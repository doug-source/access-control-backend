<?php

declare(strict_types=1);

namespace App\Services\Role\Contracts;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection as BaseCollection;

interface RoleServiceInterface
{
    /**
     * Receive role names to remove and to include into collection role
     *
     * @param \Illuminate\Database\Eloquent\Collection<int, \App\Models\Role> $roles
     * @param \Illuminate\Support\Collection<int, string> $namesToRemove
     * @param \Illuminate\Support\Collection<int, string> $namesToInclude
     * @return \Illuminate\Database\Eloquent\Collection<int, \App\Models\Role>
     */
    public function combine(Collection $roles, BaseCollection $namesToRemove, BaseCollection $namesToInclude): Collection;

    /**
     * Search by roles belong to (or not belong to) user
     */
    public function findReferenceRoles(User $user, bool $owner, int $page, int $group, ?string $name = NULL): LengthAwarePaginator;
}

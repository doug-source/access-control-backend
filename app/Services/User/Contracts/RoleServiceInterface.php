<?php

declare(strict_types=1);

namespace App\Services\User\Contracts;

use Illuminate\Database\Eloquent\Collection;
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
}

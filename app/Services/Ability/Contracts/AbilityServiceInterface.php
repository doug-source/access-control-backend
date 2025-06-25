<?php

declare(strict_types=1);

namespace App\Services\Ability\Contracts;

use App\Models\Role;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;

use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection as BaseCollection;

interface AbilityServiceInterface
{
    /**
     * Filter the available abilities from an user
     */
    public function abilitiesFromUser(User $user): BaseCollection;

    /**
     * Search by abilities belong to (or not belong to) user
     */
    public function findReferenceUserAbilities(User $user, bool $owner, int $page, int $group, ?string $name = NULL): LengthAwarePaginator;

    /**
     * Search by abilities belong to (or not belong to) role
     */
    public function findReferenceRoleAbilities(Role $role, bool $owner, int $page, int $group, ?string $name = NULL): LengthAwarePaginator;

    /**
     * Update the Role's abilities removing and inserting ability instances
     *
     * @param \App\Models\Role $role
     * @param \Illuminate\Support\Collection<int, string> $namesToRemove
     * @param \Illuminate\Support\Collection<int, string> $namesToInclude
     */
    public function updateRoleAbilities(Role $role, BaseCollection $namesToRemove, BaseCollection $namesToInclude): void;

    /**
     * Search by abilities linked directly to user into database, splitting them in
     * "to include" and "to remove" partitions
     *
     * @return array{included: Illuminate\Support\Collection<int, \App\Models\Ability>, removed: Illuminate\Support\Collection<int, \App\Models\Ability>}
     */
    public function collectSingleAbilities(User $user): array;

    /**
     * Search by summarized and distinct abilities list from user's roles
     *
     * @return \Illuminate\Support\Collection<int, \App\Models\Ability>
     */
    public function abilitiesFromUserRoles(User $user): BaseCollection;

    /**
     * Search all abilities to each collection's role instance
     *
     * @param \Illuminate\Support\Collection<int, \App\Models\Role> $collection
     * @return \Illuminate\Support\Collection<int, \Illuminate\Support\Collection<int, \App\Models\Ability>>
     */
    public function abilitiesFromRoles(BaseCollection $collection): BaseCollection;
}

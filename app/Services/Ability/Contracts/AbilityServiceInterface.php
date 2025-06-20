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
}

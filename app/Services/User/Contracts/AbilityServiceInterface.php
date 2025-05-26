<?php

declare(strict_types=1);

namespace App\Services\User\Contracts;

use App\Models\Role;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface AbilityServiceInterface
{
    /**
     * Filter the available abilities from an user
     */
    public function abilitiesFromUser(User $user): Collection;

    /**
     * Search by abilities belong to (or not belong to) user
     */
    public function findReferenceUserAbilities(User $user, bool $owner, int $page, int $group, ?string $name = NULL): LengthAwarePaginator;

    /**
     * Search by abilities belong to (or not belong to) role
     */
    public function findReferenceRoleAbilities(Role $role, bool $owner, int $page, int $group, ?string $name = NULL): LengthAwarePaginator;
}

<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Role;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection as BaseCollection;

final class RoleRepository extends AbstractRepository
{
    public function __construct()
    {
        parent::__construct(Role::class);
    }

    /**
     * Query the Role instance pagination list
     *
     * @param array<int, int> $exclude
     */
    public function paginate(int $page, int $group, ?string $name = NULL, array $exclude = []): LengthAwarePaginator
    {
        $query = $this->loadModel()::query()->select('id', 'name')->whereNotIn('id', $exclude);
        if ($name) {
            $query = $query->where([
                ['name', 'like', "%{$name}%"]
            ]);
        }
        return $query->paginate(
            page: $page,
            perPage: $group,
            columns: ['id', 'name']
        );
    }

    /**
     * Search the Role instance list based in include id list (and optionally exclude id list)
     *
     * @param array<int> $include
     * @param array<int> $exclude
     */
    public function findRoleListFiltered(int $page, int $group, ?string $name = NULL, array $include = [], array $exclude = []): LengthAwarePaginator
    {
        $query = $this->loadModel()::query()->select('id', 'name')->whereNotIn('id', $exclude);
        if ($name) {
            $query = $query->where([
                ['name', 'like', "%{$name}%"]
            ]);
        }
        if ($include) {
            $query = $query->whereIn('id', $include);
        }
        return $query->paginate(
            page: $page,
            perPage: $group,
        );
    }

    /**
     * Query the Role's Ability instance pagination list
     */
    public function paginateAbilities(Role $role, int $page, int $group, ?string $name = NULL): LengthAwarePaginator
    {
        $query = $role->abilities()->select('id', 'name');
        if ($name) {
            $query = $query->where([
                ['name', 'like', "%{$name}%"]
            ]);
        }
        return $query->paginate(
            page: $page,
            perPage: $group,
        );
    }

    /**
     * Search an Role instance by name
     */
    public function findByName(?string $name): ?Role
    {
        if (is_null($name)) {
            return NULL;
        }
        return $this->loadModel()::query()->firstWhere('name', $name);
    }

    /**
     * Search Role instances by names
     *
     * @param array<string>|Illuminate\Support\Collection<int, string> $names
     */
    public function findByNames(array|BaseCollection $names): Collection
    {
        if ($names instanceof BaseCollection) {
            $names = $names->toArray();
        }
        return $this->loadModel()::query()->whereIn('name', $names)->get();
    }
}

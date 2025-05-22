<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Role;
use Illuminate\Pagination\LengthAwarePaginator;

final class RoleRepository extends AbstractRepository
{
    public function __construct()
    {
        parent::__construct(Role::class);
    }

    /**
     * Query the Role instance pagination list
     */
    public function paginate($perPage = 3, ?string $name = NULL): LengthAwarePaginator
    {
        $query = $this->loadModel()::query();
        if ($name) {
            $query = $query->where([
                ['name', 'like', "%{$name}%"]
            ]);
        }
        return tap($query->paginate(
            perPage: $perPage,
            columns: ['id', 'name', 'created_at', 'updated_at']
        ), function (LengthAwarePaginator $paginatedInstance) {
            return $paginatedInstance->getCollection()->transform(function (Role $role) {
                return $role->ui;
            });
        });
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
}

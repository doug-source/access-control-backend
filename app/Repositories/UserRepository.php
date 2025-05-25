<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\User;
use App\Repositories\Traits\PickRoleUiProperty;
use Illuminate\Pagination\LengthAwarePaginator;

class UserRepository extends AbstractRepository
{
    use PickRoleUiProperty;

    public function __construct()
    {
        parent::__construct(User::class);
    }

    /**
     * Query the User instance pagination list
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
            columns: ['id', 'name', 'email', 'phone', 'created_at', 'updated_at', 'email_verified_at']
        ), function (LengthAwarePaginator $paginatedInstance) {
            return $paginatedInstance->getCollection()->transform(function (User $user) {
                return $user->ui;
            });
        });
    }

    /**
     * Make the User's Role instance list builder
     */
    public function findRoles(User $user, int $page, int $group, ?string $name = NULL): LengthAwarePaginator
    {
        $query = $user->roles();
        if ($name) {
            $query = $query->where([
                ['name', 'like', "%{$name}%"]
            ]);
        }
        return $this->pickRoleUi($query->paginate(
            page: $page,
            perPage: $group,
        ));
    }

    /**
     * Search an User instance by email
     */
    public function findByEmail(?string $email): ?User
    {
        if (is_null($email)) {
            return NULL;
        }
        return $this->loadModel()::query()->firstWhere('email', $email);
    }
}

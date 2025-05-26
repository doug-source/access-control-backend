<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\User;
use App\Repositories\Traits\PickUiProperty;
use Illuminate\Pagination\LengthAwarePaginator;

class UserRepository extends AbstractRepository
{
    use PickUiProperty;

    public function __construct()
    {
        parent::__construct(User::class);
    }

    /**
     * Query the User instance pagination list
     */
    public function paginate(int $page, int $group, ?string $name = NULL): LengthAwarePaginator
    {
        $query = $this->loadModel()::query();
        if ($name) {
            $query = $query->where([
                ['name', 'like', "%{$name}%"]
            ]);
        }
        return $this->pickUiSummary($query->paginate(
            page: $page,
            perPage: $group,
            columns: ['id', 'name', 'email', 'phone', 'created_at', 'updated_at', 'email_verified_at']
        ));
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
        return $this->pickUiSummary($query->paginate(
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

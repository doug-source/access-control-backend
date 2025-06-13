<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\RegisterPermission;
use Illuminate\Pagination\LengthAwarePaginator;

final class RegisterPermissionRepository extends AbstractRepository
{

    public function __construct()
    {
        parent::__construct(RegisterPermission::class);
    }

    /**
     * Query the RegisterPermission instance pagination list
     */
    public function paginate(int $page, int $group, ?string $email = NULL): LengthAwarePaginator
    {
        $query = $this->loadModel()::query()->select('id', 'email');
        if ($email) {
            $query = $query->where([
                ['email', 'like', "%{$email}%"]
            ]);
        }
        return $query->paginate(
            page: $page,
            perPage: $group,
        );
    }

    /**
     * Search an RegisterPermission instance by email
     */
    public function findByEmail(?string $email): ?RegisterPermission
    {
        if (is_null($email)) {
            return NULL;
        }
        return $this->loadModel()::query()->firstWhere('email', $email);
    }
}

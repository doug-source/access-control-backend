<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\RegisterRequest;
use Illuminate\Pagination\LengthAwarePaginator;

class RegisterRequestRepository extends AbstractRepository
{
    public function __construct()
    {
        parent::__construct(RegisterRequest::class);
    }

    /**
     * Query the RegisterRequest instance pagination list
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
     * Search an RegisterRequest instance by email
     */
    public function findByEmail(?string $email): ?RegisterRequest
    {
        if (is_null($email)) {
            return NULL;
        }
        return $this->loadModel()::query()->firstWhere('email', $email);
    }
}

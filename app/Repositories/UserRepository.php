<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\User;

class UserRepository extends AbstractRepository
{
    public function __construct()
    {
        parent::__construct(User::class);
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

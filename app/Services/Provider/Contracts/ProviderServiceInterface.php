<?php

declare(strict_types=1);

namespace App\Services\Provider\Contracts;

use Laravel\Socialite\Contracts\User as UserProvided;

interface ProviderServiceInterface
{
    /**
     * Create the user by login provider
     */
    public function createUserByProvider(UserProvided $userProvided, string $provider): void;
}

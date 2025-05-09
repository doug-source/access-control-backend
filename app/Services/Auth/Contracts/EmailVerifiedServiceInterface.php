<?php

declare(strict_types=1);

namespace App\Services\Auth\Contracts;

interface EmailVerifiedServiceInterface
{
    /**
     * Define if user has your email verified
     */
    public function userHasEmailVerified(): bool;
}

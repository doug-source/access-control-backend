<?php

declare(strict_types=1);

namespace App\Services\Auth\Contracts;

interface LoginOutputServiceInterface
{
    /**
     * Make the photo's full pathname
     */
    public function makePhotoPath(string $fileName): string;

    /**
     * Generate the output required to user's authentication
     */
    public function generate(EmailVerifiedServiceInterface $emailVerifiedService, $user): array;
}

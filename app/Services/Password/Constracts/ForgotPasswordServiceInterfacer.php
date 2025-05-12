<?php

declare(strict_types=1);

namespace App\Services\Password\Constracts;

interface ForgotPasswordServiceInterfacer
{
    /**
     * Send the password reset link requested by user
     *
     * @param array{email: string} $inputs
     * @return array{ok: bool, message: string}
     */
    public function sendResetLink(array $inputs): array;
}

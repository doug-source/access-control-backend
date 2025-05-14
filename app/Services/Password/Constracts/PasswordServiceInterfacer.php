<?php

declare(strict_types=1);

namespace App\Services\Password\Constracts;

use Closure;

interface PasswordServiceInterfacer
{
    /**
     * Send the password reset link requested by user
     *
     * @param array{email: string} $inputs
     * @return array{ok: bool, message: string}
     */
    public function sendResetLink(array $inputs): array;

    /**
     * Send the password reset requested by user
     *
     * @param array{email: string, password: string, password_confirmation: string, token: string} $credentials
     * @param \Closure(App\Models\User, string): void $callback
     * @return array{ok: bool, message: string}
     */
    public function reset(array $credentials, Closure $callback): array;
}

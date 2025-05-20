<?php

declare(strict_types=1);

namespace App\Services\Auth;

use App\Services\Auth\Contracts\EmailVerifiedServiceInterface;
use App\Services\Auth\Contracts\LoginOutputServiceInterface;
use Illuminate\Support\Str;

final class LoginOutputService implements LoginOutputServiceInterface
{
    public function generate(EmailVerifiedServiceInterface $emailVerifiedService, $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'token' => Str::replaceMatches(
                pattern: '|^\d+\||',
                replace: '',
                subject: $user->createToken('auth-app')->plainTextToken
            ),
            'email' => $user->email,
            'emailVerified' => $emailVerifiedService->userHasEmailVerified(),
        ];
    }
}

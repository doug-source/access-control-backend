<?php

declare(strict_types=1);

namespace App\Library\Builders;

use App\Services\Auth\Contracts\EmailVerifiedServiceInterface;
use Illuminate\Support\Str;

final class LoginOutput
{
    /**
     * Generate the output required to user's authentication
     */
    public static function generate(EmailVerifiedServiceInterface $emailVerifiedService, $user): array
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
            'emailVerified' => $emailVerifiedService->userHasEmailVerified()
        ];
    }
}

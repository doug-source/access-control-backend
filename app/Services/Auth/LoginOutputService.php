<?php

declare(strict_types=1);

namespace App\Services\Auth;

use App\Services\Auth\Contracts\EmailVerifiedServiceInterface;
use App\Services\Auth\Contracts\LoginOutputServiceInterface;
use App\Services\Ability\Contracts\AbilityServiceInterface;
use Illuminate\Support\Str;

final class LoginOutputService implements LoginOutputServiceInterface
{
    public function __construct(private AbilityServiceInterface $abilityService)
    {
        // ...
    }

    public function makePhotoPath(string $fileName): string
    {
        $host = request()->schemeAndHttpHost();
        return "{$host}/storage/app/{$fileName}";
    }

    /**
     * Pick the photo storaged by user
     */
    private function takePhoto($user)
    {
        if ($user->photo) {
            $host = request()->schemeAndHttpHost();
            return "{$host}/storage/app/{$user->photo}";
        }
        $providers = $user->providers;
        return $providers->count() > 0 ? $providers->first()->avatar : NULL;
    }

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
            'phone' => $user->phone,
            'photo' => $this->takePhoto($user),
            'emailVerified' => $emailVerifiedService->userHasEmailVerified(),
            'abilities' => $this->abilityService->abilitiesFromUser($user)->pluck('name')
        ];
    }
}

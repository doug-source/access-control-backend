<?php

declare(strict_types=1);

namespace App\Services\Provider;

use App\Repositories\ProviderRepository;
use App\Repositories\RegisterPermissionRepository;
use App\Repositories\UserRepository;
use App\Services\Provider\Contracts\ProviderServiceInterface;
use Laravel\Socialite\Contracts\User as UserProvided;

final class ProviderService implements ProviderServiceInterface
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly RegisterPermissionRepository $permissionRepository,
        private readonly ProviderRepository $providerRepository
    ) {
        // ...
    }

    public function createUserByProvider(UserProvided $userProvided, string $provider): void
    {
        $registerPermission = $this->permissionRepository->findByEmail($userProvided->getEmail());
        $this->permissionRepository->delete($registerPermission->id);

        $user = $this->userRepository->create([
            'name' => $userProvided->getName(),
            'email' => $userProvided->getEmail(),
            'phone' => $registerPermission->phone
        ]);
        $user->email_verified_at = now();
        $user->save();
        $this->providerRepository->create([
            'provider' => $provider,
            'provider_id' => $userProvided->getId(),
            'user_id' => $user->id,
            'avatar' => $userProvided->getAvatar()
        ]);
    }
}

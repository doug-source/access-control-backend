<?php

declare(strict_types=1);

namespace App\Providers;

use App\Repositories\RegisterPermissionRepository;
use App\Repositories\RegisterRequestRepository;
use App\Repositories\UserRepository;
use App\Services\Auth\{
    Contracts\EmailVerifiedServiceInterface,
    EmailVerifiedService
};
use App\Services\Password\Constracts\PasswordServiceInterfacer;
use App\Services\Password\PasswordService;
use App\Services\Register\RegisterService;
use App\Services\Register\RegisterServiceInterface;
use Illuminate\Support\ServiceProvider;

final class ServiceServiceProvider extends ServiceProvider
{
    /** @var array{0: string, 1: string}[] */
    private array $bindables = [
        [
            RegisterServiceInterface::class,
            RegisterService::class
        ],
        [
            EmailVerifiedServiceInterface::class,
            EmailVerifiedService::class
        ],
        [
            PasswordServiceInterfacer::class,
            PasswordService::class
        ],
        [
            RegisterPermissionRepository::class,
            RegisterPermissionRepository::class,
        ],
        [
            RegisterRequestRepository::class,
            RegisterRequestRepository::class,
        ],
        [
            UserRepository::class,
            UserRepository::class,
        ],
    ];

    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        collect($this->bindables)->each(
            fn($arrBind) => $this->app->bind(...$arrBind)
        );
    }
}

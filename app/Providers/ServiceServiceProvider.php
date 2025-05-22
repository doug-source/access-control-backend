<?php

declare(strict_types=1);

namespace App\Providers;

use App\Services\Auth\{
    Contracts\EmailVerifiedServiceInterface,
    Contracts\LoginOutputServiceInterface,
    EmailVerifiedService,
    LoginOutputService
};
use App\Services\Password\Constracts\PasswordServiceInterfacer;
use App\Services\Password\PasswordService;
use App\Services\Register\RegisterService;
use App\Services\Register\Contracts\RegisterServiceInterface;
use App\Services\User\AbilityService;
use App\Services\User\Contracts\AbilityServiceInterface;
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
            AbilityServiceInterface::class,
            AbilityService::class
        ],
        [
            LoginOutputServiceInterface::class,
            LoginOutputService::class
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

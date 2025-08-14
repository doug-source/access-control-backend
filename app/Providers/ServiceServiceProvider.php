<?php

declare(strict_types=1);

namespace App\Providers;

use App\Services\Ability\AbilityRoleService;
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
use App\Services\Ability\AbilityService;
use App\Services\Ability\AbilityUserService;
use App\Services\Ability\Contracts\AbilityRoleServiceInterface;
use App\Services\Role\RoleService;
use App\Services\Ability\Contracts\AbilityServiceInterface;
use App\Services\Ability\Contracts\AbilityUserServiceInterface;
use App\Services\Provider\Contracts\ProviderServiceInterface;
use App\Services\Provider\ProviderService;
use App\Services\Role\Contracts\RoleServiceInterface;
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
        [
            RoleServiceInterface::class,
            RoleService::class,
        ],
        [
            AbilityUserServiceInterface::class,
            AbilityUserService::class
        ],
        [
            AbilityRoleServiceInterface::class,
            AbilityRoleService::class
        ],
        [
            ProviderServiceInterface::class,
            ProviderService::class
        ]
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

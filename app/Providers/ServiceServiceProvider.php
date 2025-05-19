<?php

declare(strict_types=1);

namespace App\Providers;

use App\Repositories\RegisterPermissionRepository;
use App\Repositories\RegisterRequestRepository;
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
        $this->app->bind(
            RegisterServiceInterface::class,
            RegisterService::class
        );
        $this->app->bind(
            EmailVerifiedServiceInterface::class,
            EmailVerifiedService::class
        );
        $this->app->bind(
            PasswordServiceInterfacer::class,
            PasswordService::class
        );
        $this->app->bind(
            RegisterPermissionRepository::class,
            RegisterPermissionRepository::class,
        );
        $this->app->bind(
            RegisterRequestRepository::class,
            RegisterRequestRepository::class,
        );
    }
}

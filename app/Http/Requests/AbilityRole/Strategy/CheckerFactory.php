<?php

declare(strict_types=1);

namespace App\Http\Requests\AbilityRole\Strategy;

use App\Http\Requests\CheckerFactoryScheme;
use Illuminate\Foundation\Http\FormRequest;
use App\Http\Requests\Shared\Strategies\OwnerGet as getPlain;
use App\Http\Requests\Checker;
use App\Http\Requests\IdLinkable;
use App\Repositories\RoleRepository;

class CheckerFactory implements CheckerFactoryScheme
{
    use IdLinkable;

    public function getChecker(FormRequest $formRequest): ?Checker
    {
        $formRequest->merge(['role' => $this->buildRouteParam(
            formRequest: $formRequest,
            repository: app(RoleRepository::class),
            routeKey: 'role',
        )]);
        return new GetPlain();
    }
}

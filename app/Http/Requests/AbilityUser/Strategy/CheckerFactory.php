<?php

declare(strict_types=1);

namespace App\Http\Requests\AbilityUser\Strategy;

use App\Http\Requests\CheckerFactoryScheme;
use Illuminate\Foundation\Http\FormRequest;

use App\Http\Requests\Shared\Strategies\OwnerGet as GetPlain;
use App\Http\Requests\{
    Checker,
    IdLinkable
};
use App\Repositories\UserRepository;

class CheckerFactory implements CheckerFactoryScheme
{
    use IdLinkable;

    public function getChecker(FormRequest $formRequest): ?Checker
    {
        $formRequest->merge(['user' => $this->buildRouteParam(
            formRequest: $formRequest,
            repository: app(UserRepository::class),
            routeKey: 'user',
        )]);
        return new GetPlain();
    }
}

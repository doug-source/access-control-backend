<?php

declare(strict_types=1);

namespace App\Http\Requests\AbilityUser\Strategy;

use App\Http\Requests\CheckerFactoryScheme;
use Illuminate\Foundation\Http\FormRequest;
use App\Http\Requests\Shared\Strategies\OwnerGet as GetPlain;
use App\Http\Requests\AbilityUser\Strategy\Patch\Plain as PatchPlain;
use App\Http\Requests\{
    Checker,
    IdLinkable
};
use App\Repositories\UserRepository;
use App\Services\Ability\Contracts\AbilityServiceInterface;
use Exception;
use Illuminate\Support\Str;

class CheckerFactory implements CheckerFactoryScheme
{
    use IdLinkable;

    public function getChecker(FormRequest $formRequest): ?Checker
    {
        $method = Str::of($formRequest->method())->lower()->toString();
        switch ($method) {
            case 'patch':
                $user = $this->buildRouteParam(
                    formRequest: $formRequest,
                    repository: app(UserRepository::class),
                    routeKey: 'user',
                );
                return new PatchPlain($formRequest->merge([
                    'user' => $user,
                    'singleAbilities' => app(
                        AbilityServiceInterface::class
                    )->collectSingleAbilities($user),
                ]));
            case 'get':
                $formRequest->merge(['user' => $this->buildRouteParam(
                    formRequest: $formRequest,
                    repository: app(UserRepository::class),
                    routeKey: 'user',
                )]);
                return new GetPlain();
            default:
                throw new Exception("Validation not implemented", 1);
        }
    }
}

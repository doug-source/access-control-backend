<?php

declare(strict_types=1);

namespace App\Http\Requests\RoleUser\Strategy;

use App\Http\Requests\{
    Checker,
    CheckerFactoryScheme,
    IdLinkable
};
use App\Http\Requests\Shared\Strategies\OwnerGet as GetPlain;
use App\Http\Requests\RoleUser\Strategy\{
    Patch\Plain as PatchPlain,
};
use App\Repositories\UserRepository;
use Exception;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

final class CheckerFactory implements CheckerFactoryScheme
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
                    'rolesFromUser' => $user->roles
                ]));
            case 'get':
                $formRequest->merge(['user' => $this->buildRouteParam(
                    formRequest: $formRequest,
                    repository: app(UserRepository::class),
                    routeKey: 'user',
                    trashed: TRUE,
                )]);
                return new GetPlain();
            default:
                throw new Exception("Validation not implemented", 1);
        }
    }
}

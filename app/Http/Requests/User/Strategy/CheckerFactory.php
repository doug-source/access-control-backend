<?php

declare(strict_types=1);

namespace App\Http\Requests\User\Strategy;

use App\Http\Requests\Checker;
use App\Http\Requests\CheckerFactoryScheme;
use App\Http\Requests\User\Strategy\Get\RegisterForm;
use Illuminate\Foundation\Http\FormRequest;
use App\Http\Requests\Shared\Strategies\Get as GetPlain;
use App\Http\Requests\User\Strategy\{
    Post\Plain as PostPlain
};
use App\Repositories\RegisterPermissionRepository;
use Exception;
use Illuminate\Support\Uri;

class CheckerFactory implements CheckerFactoryScheme
{
    public function __construct(private RegisterPermissionRepository $permissionRepository)
    {
        // ...
    }

    public function getChecker(FormRequest $formRequest): ?Checker
    {
        $method = $formRequest->method();
        return match ($method) {
            'GET' => $this->selectGetChecker($formRequest),
            'POST' => new PostPlain(
                formRequest: $formRequest,
                permissionRepository: $this->permissionRepository,
            ),
        };
    }

    /**
     * Select the checker according to uri
     */
    private function selectGetChecker(FormRequest $formRequest): Checker
    {
        return match ('/' . Uri::of($formRequest->getUri())->path()) {
            route(name: 'user.create', absolute: false) => new RegisterForm(),
            route(name: 'user.index', absolute: false) => new GetPlain(),
            route(name: 'user.removed.index', absolute: false) => new GetPlain(),
            default => throw new Exception('Checker not implemented', 1),
        };
    }
}

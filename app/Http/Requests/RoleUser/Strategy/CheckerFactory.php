<?php

declare(strict_types=1);

namespace App\Http\Requests\RoleUser\Strategy;

use App\Http\Requests\{
    Checker,
    CheckerFactoryScheme
};
use App\Http\Requests\RoleUser\Strategy\{
    Get\Plain as GetPlain,
    Patch\Plain as PatchPlain,
};
use App\Models\User;
use App\Repositories\UserRepository;
use Exception;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Response;
use Illuminate\Support\Str;

final class CheckerFactory implements CheckerFactoryScheme
{
    public function getChecker(FormRequest $formRequest): ?Checker
    {
        $method = Str::of($formRequest->method())->lower()->toString();
        switch ($method) {
            case 'patch':
                return new PatchPlain($formRequest->merge(['user' => $this->buildRouteUser($formRequest)]));
            case 'get':
                $formRequest->merge(['user' => $this->buildRouteUser($formRequest)]);
                return new GetPlain();
            default:
                throw new Exception("Validation not implemented", 1);
        }
    }

    /**
     * Return the User instance based on the user route parameter
     */
    private function buildRouteUser(FormRequest $formRequest): User
    {
        $user = app(UserRepository::class)->find(
            (int) $formRequest->route('user')
        );
        if (is_null($user)) {
            abort(Response::HTTP_NOT_FOUND);
        }
        return $user;
    }
}

<?php

declare(strict_types=1);

namespace App\Http\Requests\RoleUser\Strategy;

use App\Http\Requests\{
    Checker,
    CheckerFactoryScheme
};
use App\Http\Requests\RoleUser\Strategy\{
    Patch\Plain as PatchPlain,
};
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
                $user = app(UserRepository::class)->find(
                    (int) $formRequest->route('user')
                );
                if (is_null($user)) {
                    abort(Response::HTTP_NOT_FOUND);
                }
                return new PatchPlain($formRequest->merge(['user' => $user]));
            default:
                throw new Exception("Validation not implemented", 1);
        }
    }
}

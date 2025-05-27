<?php

declare(strict_types=1);

namespace App\Http\Requests\Role\Strategy;

use App\Http\Requests\{
    Checker,
    CheckerFactoryScheme
};
use App\Http\Requests\Shared\Strategies\Get as GetPlain;
use App\Http\Requests\Role\Strategy\{
    Post\Plain as PostPlain,
    Patch\Plain as PatchPlain,
    Delete\Plain as DeletePlain,
};
use Exception;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

final class CheckerFactory implements CheckerFactoryScheme
{
    public function getChecker(FormRequest $formRequest): ?Checker
    {
        $method = Str::of($formRequest->method())->lower()->toString();
        return match ($method) {
            'patch' => new PatchPlain(),
            'post' => new PostPlain(),
            'delete' => new DeletePlain(),
            'get' => new GetPlain(),
            default => throw new Exception("Validation not implemented", 1)
        };
    }
}

<?php

declare(strict_types=1);

namespace App\Http\Requests\Ability\Strategy;

use App\Http\Requests\{
    Checker,
    CheckerFactoryScheme
};
use App\Http\Requests\Ability\Strategy\{
    Patch\Plain as PatchPlain,
    Delete\Plain as DeletePlain
};
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

final class CheckerFactory implements CheckerFactoryScheme
{
    public function getChecker(FormRequest $formRequest): ?Checker
    {
        if (Str::of($formRequest->method())->lower()->toString() === 'patch') {
            return new PatchPlain();
        }
        return new DeletePlain();
    }
}

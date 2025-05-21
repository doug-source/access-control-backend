<?php

declare(strict_types=1);

namespace App\Http\Requests\Ability\Strategy;

use App\Http\Requests\{
    Checker,
    CheckerFactoryScheme
};
use App\Http\Requests\Ability\Strategy\Patch\{
    Plain as PatchPlain,
};
use Illuminate\Foundation\Http\FormRequest;

final class CheckerFactory implements CheckerFactoryScheme
{
    public function getChecker(FormRequest $formRequest): ?Checker
    {
        return new PatchPlain();
    }
}

<?php

declare(strict_types=1);

namespace App\Http\Requests\AbilityUser\Strategy;

use App\Http\Requests\CheckerFactoryScheme;
use Illuminate\Foundation\Http\FormRequest;
use App\Http\Requests\AbilityUser\Strategy\{
    Get\Plain as GetPlain,
};
use App\Http\Requests\Checker;

class CheckerFactory implements CheckerFactoryScheme
{
    public function getChecker(FormRequest $formRequest): ?Checker
    {
        return new GetPlain();
    }
}

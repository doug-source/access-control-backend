<?php

declare(strict_types=1);

namespace App\Http\Requests\ForgotPassword\Strategy;

use App\Http\Requests\Checker;
use App\Http\Requests\CheckerFactoryScheme;
use Illuminate\Foundation\Http\FormRequest;
use App\Http\Requests\ForgotPassword\Strategy\Get\Plain as GetPlain;

final class CheckerFactory implements CheckerFactoryScheme
{
    public function getChecker(FormRequest $formRequest): ?Checker
    {
        return new GetPlain();
    }
}

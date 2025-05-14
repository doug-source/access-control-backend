<?php

declare(strict_types=1);

namespace App\Http\Requests\ResetPassword\Strategy;

use App\Http\Requests\Checker;
use App\Http\Requests\CheckerFactoryScheme;
use Illuminate\Foundation\Http\FormRequest;
use App\Http\Requests\ResetPassword\Strategy\Post\Plain as PostPlain;

final class CheckerFactory implements CheckerFactoryScheme
{
    public function getChecker(FormRequest $formRequest): ?Checker
    {
        return new PostPlain();
    }
}

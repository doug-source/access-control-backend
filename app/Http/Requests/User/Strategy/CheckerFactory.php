<?php

declare(strict_types=1);

namespace App\Http\Requests\User\Strategy;

use App\Http\Requests\Checker;
use App\Http\Requests\CheckerFactoryScheme;
use App\Http\Requests\User\Strategy\Get\RegisterForm;
use Illuminate\Foundation\Http\FormRequest;
use App\Http\Requests\User\Strategy\Post\Plain as PostPlain;

class CheckerFactory implements CheckerFactoryScheme
{
    public function getChecker(FormRequest $formRequest): ?Checker
    {
        if ($formRequest->isMethod('GET')) {
            return new RegisterForm();
        }
        return new PostPlain($formRequest);
    }
}

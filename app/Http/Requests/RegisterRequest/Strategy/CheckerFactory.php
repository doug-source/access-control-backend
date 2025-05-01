<?php

namespace App\Http\Requests\RegisterRequest\Strategy;

use App\Http\Requests\Checker;
use Illuminate\Foundation\Http\FormRequest;
use App\Http\Requests\CheckerFactoryScheme;
use App\Http\Requests\RegisterRequest\Strategy\{
    Get\Plain as GetChecker
};

class CheckerFactory implements CheckerFactoryScheme
{
    public function getChecker(FormRequest $formRequest): ?Checker
    {
        return new GetChecker();
    }
}

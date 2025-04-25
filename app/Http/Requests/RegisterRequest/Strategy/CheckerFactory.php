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
    /**
     * Return the Checker instance based on FormRequest instance
     */
    public function getChecker(FormRequest $formRequest): ?Checker
    {
        return new GetChecker();
    }
}

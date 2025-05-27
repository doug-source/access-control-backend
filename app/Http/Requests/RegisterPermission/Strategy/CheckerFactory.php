<?php

namespace App\Http\Requests\RegisterPermission\Strategy;

use App\Http\Requests\Checker;
use Illuminate\Foundation\Http\FormRequest;
use App\Http\Requests\CheckerFactoryScheme;
use App\Http\Requests\RegisterPermission\Strategy\{
    Get\EmailGet as IndexPlain,
    Get\Show as ShowPlain,
};
use Illuminate\Support\Str;

class CheckerFactory implements CheckerFactoryScheme
{
    public function getChecker(FormRequest $formRequest): ?Checker
    {
        $indexRoute = route(name: 'register.permission.index', absolute: FALSE);
        if (Str::of($formRequest->getPathInfo())->endsWith($indexRoute)) {
            return new IndexPlain();
        }
        return new ShowPlain();
    }
}

<?php

namespace App\Http\Requests\RegisterRequest\Strategy;

use App\Http\Requests\Checker;
use Illuminate\Foundation\Http\FormRequest;
use App\Http\Requests\CheckerFactoryScheme;
use App\Http\Requests\RegisterRequest\Strategy\{
    Get\EmailGet as IndexPlain,
    Get\Show as ShowPlain,
    Post\Plain as PostPlain,
    Delete\Plain as DeletePlain
};
use Illuminate\Support\Str;

class CheckerFactory implements CheckerFactoryScheme
{
    public function getChecker(FormRequest $formRequest): ?Checker
    {
        if ($formRequest->isMethod('GET')) {
            return $this->defineGet($formRequest);
        }
        if ($formRequest->isMethod('DELETE')) {
            return new DeletePlain();
        }
        return new PostPlain();
    }

    private function defineGet(FormRequest $formRequest)
    {
        $indexRoute = route(name: 'register.request.index', absolute: FALSE);
        if (Str::of($formRequest->getPathInfo())->endsWith($indexRoute)) {
            return new IndexPlain();
        }
        return new ShowPlain();
    }
}

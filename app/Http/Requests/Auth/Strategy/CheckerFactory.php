<?php

namespace App\Http\Requests\Auth\Strategy;

use App\Http\Requests\Checker;
use Illuminate\Foundation\Http\FormRequest;
use App\Http\Requests\Auth\Strategy\Post\Plain as PostPlain;

class CheckerFactory
{
    /**
     * Returns the Checker instance based on FormRequest instance
     */
    public static function getChecker(FormRequest $formRequest): ?Checker
    {
        return new PostPlain($formRequest);
    }
}

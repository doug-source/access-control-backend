<?php

namespace App\Http\Requests\Auth\Strategy;

use App\Http\Requests\Checker;
use Illuminate\Foundation\Http\FormRequest;
use App\Http\Requests\Auth\Strategy\Post\{
    Login as LoginPost,
};

class CheckerFactory
{
    /**
     * Return the Checker instance based on FormRequest instance
     */
    public static function getChecker(FormRequest $formRequest): ?Checker
    {
        return new LoginPost();
    }

}

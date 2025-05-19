<?php

namespace App\Http\Requests\Auth\Strategy;

use App\Http\Requests\Checker;
use Illuminate\Foundation\Http\FormRequest;
use App\Http\Requests\Auth\Strategy\Post\{
    Login as LoginPost,
};
use App\Http\Requests\CheckerFactoryScheme;
use App\Repositories\UserRepository;

class CheckerFactory implements CheckerFactoryScheme
{
    public function __construct(private readonly UserRepository $userRepository)
    {
        // ...
    }

    public function getChecker(FormRequest $formRequest): ?Checker
    {
        return new LoginPost($this->userRepository);
    }
}

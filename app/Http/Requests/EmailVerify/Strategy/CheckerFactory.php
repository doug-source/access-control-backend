<?php

declare(strict_types=1);

namespace App\Http\Requests\EmailVerify\Strategy;

use App\Http\Requests\Checker;
use App\Http\Requests\CheckerFactoryScheme;
use Illuminate\Foundation\Http\FormRequest;
use App\Http\Requests\EmailVerify\Strategy\Post\Resend;
use App\Repositories\UserRepository;

final class CheckerFactory implements CheckerFactoryScheme
{
    public function __construct(private readonly UserRepository $userRepository)
    {
        // ...
    }

    public function getChecker(FormRequest $formRequest): ?Checker
    {
        return new Resend($this->userRepository);
    }
}

<?php

declare(strict_types=1);

namespace App\Http\Requests\ForgotPassword\Strategy;

use App\Http\Requests\Checker;
use App\Http\Requests\CheckerFactoryScheme;
use Illuminate\Foundation\Http\FormRequest;
use App\Http\Requests\ForgotPassword\Strategy\Get\Plain as GetPlain;
use App\Repositories\UserRepository;

final class CheckerFactory implements CheckerFactoryScheme
{
    public function __construct(private readonly UserRepository $userRepository)
    {
        // ...
    }

    public function getChecker(FormRequest $formRequest): ?Checker
    {
        return new GetPlain(userRepository: $this->userRepository);
    }
}

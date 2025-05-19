<?php

declare(strict_types=1);

namespace App\Http\Requests\ForgotPassword;

use App\Http\Requests\ForgotPassword\Strategy\CheckerFactory;
use App\Http\Requests\VerifyRequest;
use App\Repositories\UserRepository;

final class CheckRequest extends VerifyRequest
{
    public function __construct(UserRepository $userRepository)
    {
        parent::__construct(new CheckerFactory($userRepository));
    }

    public function authorize(): bool
    {
        return $this->isLoggedIn() === FALSE;
    }
}

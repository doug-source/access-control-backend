<?php

namespace App\Http\Requests\Auth;

use App\Http\Requests\Auth\Strategy\CheckerFactory;
use App\Http\Requests\VerifyRequest;
use App\Repositories\UserRepository;

class CheckRequest extends VerifyRequest
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

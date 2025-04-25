<?php

namespace App\Http\Requests\Auth;

use App\Http\Requests\Auth\Strategy\CheckerFactory;
use App\Http\Requests\VerifyRequest;

class CheckRequest extends VerifyRequest
{
    public function __construct()
    {
        parent::__construct(new CheckerFactory());
    }

    public function authorize(): bool
    {
        return $this->isLoggedIn() === FALSE;
    }
}

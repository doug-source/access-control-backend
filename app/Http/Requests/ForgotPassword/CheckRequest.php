<?php

declare(strict_types=1);

namespace App\Http\Requests\ForgotPassword;

use App\Http\Requests\ForgotPassword\Strategy\CheckerFactory;
use App\Http\Requests\VerifyRequest;

final class CheckRequest extends VerifyRequest
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

<?php

declare(strict_types=1);

namespace App\Http\Requests\RegisterRequest;

use App\Http\Requests\VerifyRequest;
use App\Http\Requests\RegisterRequest\Strategy\CheckerFactory;

final class CheckRequest extends VerifyRequest
{
    public function __construct()
    {
        parent::__construct(new CheckerFactory());
    }

    public function authorize(): bool
    {
        return $this->isLoggedIn();
    }
}

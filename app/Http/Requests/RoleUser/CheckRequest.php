<?php

declare(strict_types=1);

namespace App\Http\Requests\RoleUser;

use App\Http\Requests\RoleUser\Strategy\CheckerFactory;
use App\Http\Requests\VerifyRequest;

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

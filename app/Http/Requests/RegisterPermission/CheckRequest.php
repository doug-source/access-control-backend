<?php

declare(strict_types=1);

namespace App\Http\Requests\RegisterPermission;

use App\Http\Requests\VerifyRequest;
use App\Http\Requests\RegisterPermission\Strategy\CheckerFactory;

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

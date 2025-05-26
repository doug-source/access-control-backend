<?php

declare(strict_types=1);

namespace App\Http\Requests\AbilityUser;

use App\Http\Requests\AbilityUser\Strategy\CheckerFactory;
use App\Http\Requests\VerifyRequest;
use Illuminate\Foundation\Http\FormRequest;

class CheckRequest extends VerifyRequest
{
    public function __construct(FormRequest $formRequest)
    {
        parent::__construct(
            factory: new CheckerFactory(),
        );
    }

    public function authorize(): bool
    {
        return $this->isLoggedIn();
    }
}

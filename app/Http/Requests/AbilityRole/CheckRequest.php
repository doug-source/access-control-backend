<?php

declare(strict_types=1);

namespace App\Http\Requests\AbilityRole;

use App\Http\Requests\AbilityRole\Strategy\CheckerFactory;
use App\Http\Requests\VerifyRequest;
use Illuminate\Foundation\Http\FormRequest;

final class CheckRequest extends VerifyRequest
{
    public function __construct(FormRequest $formRequest)
    {
        parent::__construct(
            factory: new CheckerFactory($formRequest),
        );
    }

    public function authorize(): bool
    {
        return $this->isLoggedIn();
    }
}

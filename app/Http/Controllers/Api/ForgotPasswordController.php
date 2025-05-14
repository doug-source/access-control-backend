<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ForgotPassword\CheckRequest;
use App\Library\Builders\Response as ResponseBuilder;
use App\Services\Password\Constracts\PasswordServiceInterfacer;

class ForgotPasswordController extends Controller
{
    public function __construct(private readonly PasswordServiceInterfacer $passwordService)
    {
        // ...
    }

    /**
     * Request by forgot password
     */
    public function askForgotPass(CheckRequest $request)
    {
        ['ok' => $ok, 'message' => $message] = $this->passwordService->sendResetLink(
            $request->only('email')
        );
        if (!$ok) {
            return ResponseBuilder::invalidJSON($message);
        }
        return ResponseBuilder::successJSON(data: $message);
    }
}

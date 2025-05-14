<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ResetPassword\CheckRequest;
use App\Library\Builders\Response as ResponseBuilder;
use App\Models\User;
use App\Services\Password\Constracts\PasswordServiceInterfacer;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ResetPasswordController extends Controller
{
    public function __construct(private readonly PasswordServiceInterfacer $passwordService)
    {
        // ...
    }

    public function reset(CheckRequest $request)
    {
        $status = $this->passwordService->reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                $user->forceFill([
                    'password' => Hash::make($password)
                ])->setRememberToken(Str::random(60));

                $user->save();

                event(new PasswordReset($user));
            }
        );
        if ($status['ok'] === FALSE) {
            return ResponseBuilder::invalidJSON($status['message']);
        }
        return ResponseBuilder::successJSON($status['message']);
    }
}

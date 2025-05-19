<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\CheckRequest;
use App\Library\Builders\LoginOutput as LoginOutputBuilder;
use App\Library\Builders\Phrase;
use App\Library\Builders\Response as ResponseBuilder;
use App\Library\Enums\PhraseKey;
use App\Services\Auth\Contracts\EmailVerifiedServiceInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function __construct(private EmailVerifiedServiceInterface $emailVerifiedService)
    {
        // ...
    }

    /**
     * Execute the application login process
     */
    public function login(CheckRequest $request)
    {
        $credentials = $request->only('email', 'password');
        if (!Auth::attempt($credentials)) {
            return ResponseBuilder::invalidJSON(
                Phrase::pickSentence(PhraseKey::LoginInvalid)
            );
        }

        return ResponseBuilder::successJSON(data: [
            'user' => LoginOutputBuilder::generate($this->emailVerifiedService, $request->user())
        ]);
    }

    /**
     * Execute the application logout process
     */
    public function logout(Request $request)
    {
        $user = $request->user();
        $user->tokens()->delete();
        return ResponseBuilder::successJSON();
    }
}

<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\CheckRequest;
use App\Library\Builders\Response as ResponseBuilder;
use Illuminate\Http\Request;

use Illuminate\Support\{
    Facades\Auth,
    Str
};

class AuthController extends Controller
{
    /**
     * Execute the application login process
     */
    public function login(CheckRequest $request)
    {
        $credentials = $request->only('email', 'password');
        if (!Auth::attempt($credentials)) {
            return ResponseBuilder::invalidJSON(
                __('log-in') . ' ' . __('invalid')
            );
        }
        $user = $request->user();
        return ResponseBuilder::successJSON([
            'message' => 'Authorized',
            'status' => 200,
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'enterprise' => [
                        'name' => $user->enterprise->name
                    ],
                    'token' => Str::replaceMatches(
                        pattern: '|^\d+\||',
                        replace: '',
                        subject: $user->createToken('auth-app')->plainTextToken
                    )
                ]
            ]
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

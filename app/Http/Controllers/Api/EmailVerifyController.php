<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\EmailVerify\CheckRequest;
use App\Library\Builders\Response as ResponseBuilder;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class EmailVerifyController extends Controller
{
    public function verify($id)
    {
        $user = User::find($id);
        if (!$user->hasVerifiedEmail()) {
            $user->markEmailAsVerified();
        }
        return ResponseBuilder::successJSON();
    }

    /**
     * Execute the email verification resend logic
     */
    public function resend(CheckRequest $request)
    {
        $user = User::find(Auth::user()->id);
        $user->sendEmailVerificationNotification();
        return ResponseBuilder::successJSON();
    }
}

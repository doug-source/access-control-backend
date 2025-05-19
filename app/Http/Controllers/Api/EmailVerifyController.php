<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\EmailVerify\CheckRequest;
use App\Library\Builders\Response as ResponseBuilder;
use App\Repositories\UserRepository;
use Illuminate\Support\Facades\Auth;

class EmailVerifyController extends Controller
{
    public function __construct(private readonly UserRepository $userRepository)
    {
        // ...
    }

    /**
     * Execute the email verification logic
     */
    public function verify($id)
    {
        $user = $this->userRepository->find($id);
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
        $user = $this->userRepository->find(Auth::user()->id);
        $user->sendEmailVerificationNotification();
        return ResponseBuilder::successJSON();
    }
}

<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Library\Builders\Response as ResponseBuilder;
use App\Services\Auth\Contracts\{
    EmailVerifiedServiceInterface,
    LoginOutputServiceInterface
};

class SocialiteController extends Controller
{
    public function __construct(
        private EmailVerifiedServiceInterface $emailVerifiedService,
        private LoginOutputServiceInterface $loginOutputService,
    ) {
        // ...
    }

    public function releaseToken(Request $request)
    {
        $user = $request->user();
        $user->currentAccessToken()->delete();
        return ResponseBuilder::successJSON(data: [
            'user' => $this->loginOutputService->generate($this->emailVerifiedService, $request->user())
        ]);
    }
}

<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Library\Builders\Response as ResponseBuilder;
use App\Services\Auth\Contracts\EmailVerifiedServiceInterface;
use App\Library\Builders\LoginOutput as LoginOutputBuilder;

class SocialiteController extends Controller
{
    public function __construct(private EmailVerifiedServiceInterface $emailVerifiedService)
    {
        // ...
    }

    public function releaseToken(Request $request)
    {
        $user = $request->user();
        $user->currentAccessToken()->delete();
        return ResponseBuilder::successJSON(data: [
            'user' => LoginOutputBuilder::generate($this->emailVerifiedService, $request->user())
        ]);
    }
}

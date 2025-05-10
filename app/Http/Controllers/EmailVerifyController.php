<?php

namespace App\Http\Controllers;

use App\Library\Builders\UrlExternal;
use Illuminate\Foundation\Auth\EmailVerificationRequest;

class EmailVerifyController extends Controller
{
    /**
     * Receive the verification from user email
     */
    public function verify(EmailVerificationRequest $request)
    {
        $request->fulfill();
        return UrlExternal::build()->redirect();
    }

    /**
     * Redirect the user to interface login page
     */
    public function redirectLogin()
    {
        return UrlExternal::build()->redirect();
    }
}

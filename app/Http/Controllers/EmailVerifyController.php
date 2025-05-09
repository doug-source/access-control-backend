<?php

namespace App\Http\Controllers;

use App\Library\Builders\UrlExternal;
use Illuminate\Foundation\Auth\EmailVerificationRequest;

class EmailVerifyController extends Controller
{
    /**
     * Redirect the user during email verify action
     */
    public function newVerifyEmail()
    {
        return UrlExternal::build(
            path: config('app.frontend.uri.email-verify.form')
        )->redirect();
    }

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

<?php

namespace App\Http\Controllers;

use App\Library\Builders\UrlExternal;

class EmailVerifyController extends Controller
{
    /**
     * Redirect the user to interface login page
     */
    public function redirectLogin()
    {
        return UrlExternal::build()->redirect();
    }
}

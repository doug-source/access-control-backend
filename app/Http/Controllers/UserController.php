<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\CheckRequest;
use App\Library\Builders\UrlExternal;
use Illuminate\Support\Uri;

class UserController extends Controller
{
    /**
     * Redirect to the user register form view.
     * Used by template email to redirect the user to register form
     */
    public function create(CheckRequest $request)
    {
        return UrlExternal::build(
            path: config('app.frontend.uri.register.form'),
            query: ['token' => $request->token]
        )->redirect();
    }
}

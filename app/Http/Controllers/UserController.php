<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\CheckRequest;
use Illuminate\Support\Uri;

class UserController extends Controller
{
    /**
     * Redirect to the user register form view.
     * Used by template email to redirect the user to register form
     */
    public function create(CheckRequest $request)
    {
        $url = route(
            // Route the form submits the user form data to end the user register
            name: 'users.store',
            parameters: ['token' => $request->token],
            absolute: false
        );

        return Uri::of(
            config('app.frontend.uri.host')
        )->withPath(
            config('app.frontend.uri.register.form')
        )->withQuery(['action' => $url])->redirect();
    }
}

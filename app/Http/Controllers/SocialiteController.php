<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Library\Builders\UrlExternal;
// use App\Library\Builders\Response as ResponseBuilder;
use App\Library\Validators\ProviderLogin as ProviderLoginValidator;
use App\Models\User;
// use GuzzleHttp\Exception\ClientException;
use Illuminate\Http\RedirectResponse;
// use Illuminate\Http\Request;
// use Illuminate\Support\Facades\Validator;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Contracts\User as UserProvided;

class SocialiteController extends Controller
{
    /**
     * Execute the provider's redirect logic
     */
    public function redirectToProvider($provider)
    {
        return Socialite::driver($provider)->redirect();
    }

    /**
     * Execute the provider's callback logic during the authentication
     */
    public function handleProvideCallbackToLogin($provider)
    {
        $errors = (new ProviderLoginValidator($provider))->validate();
        if ($errors) {
            return UrlExternal::build(query: [
                'errormsg' => array_pop($errors)
            ])->redirect();
        }
        return $this->providerUserResponse(Socialite::driver($provider)->user());
    }

    /**
     * Build the redirect response used by provider callback during the authentication
     */
    protected function providerUserResponse(UserProvided $userProvided): RedirectResponse
    {
        $user = User::where('email', $userProvided->getEmail())->first();
        return UrlExternal::build(
            query: [
                'provided' => $user->createToken('Sanctum+Socialite+temporary')->plainTextToken
            ]
        )->redirect();
    }
}

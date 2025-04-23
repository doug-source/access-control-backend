<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Library\Validators\ProviderLogin as ProviderLoginValidator;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Laravel\Socialite\Facades\Socialite;

class SocialiteController extends Controller
{
    /**
     * Execute the provider's redirect logic
     */
    public function redirectToProvider($provider, $enterpriseID)
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
            return redirect()->away($errors['url']);
        }
        return $this->providerUserResponse(Socialite::driver($provider)->user());
    }

    /**
     * Build the redirect response used by provider callback during the authentication
     */
    protected function providerUserResponse(\Laravel\Socialite\Contracts\User $providerUser): RedirectResponse
    {
        $user = User::where('email', $providerUser->getEmail())->first();
        $qs = http_build_query([
            'provided' => $user->createToken('Sanctum+Socialite+temporary')->plainTextToken
        ]);
        $frontendUrl = config('app.frontend-url');
        return redirect()->away("{$frontendUrl}?{$qs}");
    }
}

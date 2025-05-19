<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Library\Builders\UrlExternal;
// use App\Library\Builders\Response as ResponseBuilder;
use App\Library\Validators\ProviderLogin as ProviderLoginValidator;
use App\Repositories\UserRepository;
// use GuzzleHttp\Exception\ClientException;
use Illuminate\Http\RedirectResponse;
// use Illuminate\Http\Request;
// use Illuminate\Support\Facades\Validator;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Contracts\User as UserProvided;

class SocialiteController extends Controller
{
    public function __construct(private readonly UserRepository $userRepository)
    {
        // ...
    }

    /**
     * Execute the provider's redirect logic
     */
    public function redirectToProvider($provider, $type)
    {
        session()->put('type', $type);
        return Socialite::driver($provider)->redirect();
    }

    /**
     * Execute the provider's callback logic
     */
    public function handleProvideCallback($provider)
    {
        $type = session('type');
        session()->forget('type');
        switch ($type) {
            case 'login':
                return $this->handleProvideCallbackToLogin($provider);
            case 'register':
                return $this->handleProvideCallbackToRegister($provider);
        }
    }

    /**
     * Execute the provider's callback logic during the authentication
     */
    private function handleProvideCallbackToLogin($provider)
    {
        $errors = (new ProviderLoginValidator(
            provider: $provider,
            userRepository: $this->userRepository
        ))->validate();
        if ($errors) {
            return UrlExternal::build(query: [
                'errormsg' => array_pop($errors)
            ])->redirect();
        }
        return $this->providerUserResponse(Socialite::driver($provider)->user());
    }

    /**
     * Execute the provider's callback logic during the request register
     */
    private function handleProvideCallbackToRegister($provider)
    {
        $userProvided = Socialite::driver($provider)->user();
        return UrlExternal::build(
            path: config('app.frontend.uri.register.request'),
            query: [
                'provided' => $userProvided->getEmail()
            ]
        )->redirect();
    }

    // public function handleProviderCallback($provider)
    // {
    // $validator = Validator::make($request->only('provider', 'access_provider_token'), [
    //     'provider' => ['required', 'string'],
    //     'access_provider_token' => ['required', 'string']
    // ]);
    // if ($validator->fails()) {
    //     return response()->json($validator->errors(), 400);
    // }
    // ------------------------------------------------------------------------

    //     $validated = $this->validateProvider($provider);
    //     if (!is_null($validated)) {
    //         return $validated;
    //     }

    //     try {
    //         $providerUser = Socialite::driver($provider)->user();
    //     } catch (ClientException $th) {
    //         return ResponseBuilder::invalidJSON('invalid credentials provided.');
    //     }

    //     $user = $this->buildUserProvided($providerUser, $provider);
    //     $qs = http_build_query([
    //         'provided' => $user->createToken('Sanctum+Socialite+temporary')->plainTextToken
    //     ]);

    //     $frontendUrl = config('app.frontend.uri.host');
    //     return redirect()->away("{$frontendUrl}?{$qs}");
    // }

    // protected function validateProvider($provider)
    // {
    //     if (!in_array($provider, ['google'])) {
    //         return ResponseBuilder::invalidJSON('Please login using google');
    //     }
    // }

    // protected function buildUserProvided(UserProvided $providerUser, string $provider)
    // {
    //     $user = $this->makeUser($providerUser);
    //     $this->makeProvider($providerUser, $user, $provider);

    //     return $user;
    // }

    // protected function makeUser(UserProvided $providerUser)
    // {
    //     $user = User::where(
    //         'email',
    //         $providerUser->getEmail()
    //     )->first();
    //     if (is_null($user)) {
    //         $user = new User;
    //         $user->email = $providerUser->getEmail();
    //         $user->name = $providerUser->getName();
    //         $user->email_verified_at = now();
    //         $user->save();
    //     }
    //     return $user;
    // }

    // protected function makeProvider(UserProvided $providerUser, User $user, string $provider)
    // {
    //     $user->providers()->updateOrCreate(
    //         [
    //             'provider' => $provider,
    //             'provider_id' => $providerUser->getId(),
    //         ],
    //         [
    //             'avatar' => $providerUser->getAvatar()
    //         ]
    //     );
    // }

    /**
     * Build the redirect response used by provider callback during the authentication
     */
    protected function providerUserResponse(UserProvided $userProvided): RedirectResponse
    {
        $user = $this->userRepository->findByEmail(email: $userProvided->getEmail());
        return UrlExternal::build(
            query: [
                'provided' => $user->createToken('Sanctum+Socialite+temporary')->plainTextToken
            ]
        )->redirect();
    }
}

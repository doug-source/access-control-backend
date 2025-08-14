<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Library\Builders\Phrase;
use App\Library\Builders\UrlExternal;
use App\Library\Enums\PhraseKey;
use App\Library\Validators\ProviderLogin as ProviderLoginValidator;
use App\Library\Validators\ProviderRegister as ProviderRegisterValidator;
use App\Repositories\UserRepository;
use App\Services\Provider\Contracts\ProviderServiceInterface;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Http\RedirectResponse;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Contracts\User as UserProvided;

class SocialiteController extends Controller
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly ProviderServiceInterface $providerService,
    ) {
        // ...
    }

    /**
     * Execute the provider's redirect logic
     */
    public function redirectToProvider($provider, $type, ?string $token = null)
    {
        $session = session();
        $session->put('type', $type);
        $session->put('token', $token);
        return Socialite::driver($provider)->redirect();
    }

    /**
     * Execute the provider's callback logic
     */
    public function handleProvideCallback($provider, $token = NULL)
    {
        $session = session();
        $type = $session->pull('type');
        $token = $session->pull('token');

        switch ($type) {
            case 'login':
                return $this->handleProvideCallbackToLogin($provider);
            case 'register':
                return $this->handleProvideCallbackToRegister($provider, $token);
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
    private function handleProvideCallbackToRegister($provider, ?string $token)
    {
        try {
            $userProvided = Socialite::driver($provider)->user();
        } catch (ClientException $th) {
            return UrlExternal::build(query: [
                'errormsg' => Phrase::pickSentence(PhraseKey::ProviderCredentialsInvalid)
            ])->redirect();
        }

        $errors = (new ProviderRegisterValidator(
            provider: $provider,
            email: $userProvided->getEmail(),
            token: $token
        ))->validate();
        if ($errors) {
            return UrlExternal::build(query: [
                'errormsg' => array_pop($errors)
            ])->redirect();
        }
        $this->providerService->createUserByProvider($userProvided, $provider);
        return $this->providerUserResponse($userProvided);
    }

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

<?php

namespace App\Rules;

use App\Library\Builders\Phrase;
use App\Library\Enums\PhraseKey;
use App\Repositories\UserRepository;
use Closure;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Contracts\Validation\ValidationRule;
use Laravel\Socialite\Facades\Socialite;

class ProviderUserLinked implements ValidationRule
{
    /** @var string */
    protected $provider;

    protected UserRepository $userRepository;

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($provider, UserRepository $userRepository)
    {
        $this->provider = $provider;
        $this->userRepository = $userRepository;
    }

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        try {
            $providerUser = Socialite::driver($this->provider)->user();
            $user = $this->userRepository->findByEmail($providerUser->getEmail());

            if (is_null($user)) {
                $fail(Phrase::pickSentence(PhraseKey::UserNullable));
            } else if (!is_null($user->password)) {
                $fail(Phrase::pickSentence(PhraseKey::PasswordNotNullable));
            }
        } catch (ClientException $th) {
            $fail(Phrase::pickSentence(PhraseKey::ProviderInvalid));
        }
    }
}

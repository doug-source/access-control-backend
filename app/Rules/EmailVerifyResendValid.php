<?php

namespace App\Rules;

use App\Library\Builders\Phrase;
use App\Library\Enums\PhraseKey;
use App\Repositories\UserRepository;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class EmailVerifyResendValid implements ValidationRule
{
    public function __construct(private readonly UserRepository $userRepository)
    {
        // ...
    }

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $user = $this->userRepository->find($value);
        if ($user->hasVerifiedEmail()) {
            $fail(
                Phrase::pickSentence(PhraseKey::EmailAlreadyVerified)
            );
        }
    }
}

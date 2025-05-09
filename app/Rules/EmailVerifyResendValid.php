<?php

namespace App\Rules;

use App\Library\Builders\Phrase;
use App\Library\Enums\PhraseKey;
use App\Models\User;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class EmailVerifyResendValid implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $user = User::find($value);
        if ($user->hasVerifiedEmail()) {
            $fail(
                Phrase::pickSentence(PhraseKey::EmailAlreadyVerified)
            );
        }
    }
}

<?php

namespace App\Rules;

use App\Library\Builders\Phrase;
use App\Library\Enums\PhraseKey;
use App\Models\User;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class UserNotProvided implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $user = User::where('email', $value)->first();
        if ($user && !$user->providers()->getResults()->isEmpty()) {
            $fail(Phrase::pickSentence(PhraseKey::LoginByProvider));
        }
    }
}

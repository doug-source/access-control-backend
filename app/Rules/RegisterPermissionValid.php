<?php

namespace App\Rules;

use App\Library\Builders\Phrase;
use App\Library\Enums\PhraseKey;
use App\Models\RegisterPermission;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Carbon\Carbon;

class RegisterPermissionValid implements ValidationRule
{
    /** @var \Illuminate\Database\Eloquent\Model|object|static|null */
    private $allowed;

    private ?string $token;

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct(?string $email, ?string $token)
    {
        $this->allowed = !is_null($email) ? RegisterPermission::where('email', $email)->first() : null;
        $this->token = $token;
    }

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($this->allowed?->token !== $this->token) {
            $fail(Phrase::pickSentence(PhraseKey::ParameterInvalid));
        } else if (Carbon::now()->greaterThan(Carbon::parse($this->allowed->expiration_data))) {
            $fail(Phrase::pickSentence(PhraseKey::RegistrationExpired));
        }
    }
}

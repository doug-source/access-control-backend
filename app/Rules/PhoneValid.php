<?php

namespace App\Rules;

use App\Library\Builders\Phrase;
use App\Library\Converters\Phone as PhoneConverter;
use App\Library\Enums\PhraseKey;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Str;

final class PhoneValid implements ValidationRule
{
    /**
     * Constructor of this PhoneValid
     */
    public function __construct(private readonly int $phoneMaxSize)
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
        $phone = Str::of(PhoneConverter::chopSeparators($value));
        if (!$phone->isMatch('|^\d+$|')) {
            $fail(
                Phrase::pickSentence(PhraseKey::ParameterInvalid)
            );
        } else if ($phone->length() > $this->phoneMaxSize) {
            $fail(
                Phrase::pickSentence(PhraseKey::MaxSizeInvalid, " ($this->phoneMaxSize)")
            );
        }
    }
}

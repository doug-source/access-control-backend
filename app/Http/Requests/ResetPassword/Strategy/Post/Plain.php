<?php

declare(strict_types=1);

namespace App\Http\Requests\ResetPassword\Strategy\Post;

use App\Http\Requests\Checker;
use App\Library\Builders\Phrase;
use App\Library\Chains\Password\Handlers\MinSize;
use App\Library\Chains\Password\Handlers\QtyDigits;
use App\Library\Chains\Password\Handlers\QtyLetters;
use App\Library\Chains\Password\Handlers\QtyLowercase;
use App\Library\Chains\Password\Handlers\QtySpecialChars;
use App\Library\Chains\Password\Handlers\QtyUppercase;
use App\Library\Enums\PasswordRules;
use App\Library\Enums\PhraseKey;
use App\Rules\PasswordValid;
use Illuminate\Foundation\Http\FormRequest;

final class Plain implements Checker
{
    public function all(FormRequest $formRequest, array $requestInputs): array
    {
        return [
            ...$requestInputs,
        ];
    }

    public function rules(): array
    {
        return [
            'token' => 'required',
            'email' => 'required|email',
            'password' => [
                'bail',
                'required',
                'confirmed',
                new PasswordValid(
                    new MinSize(PasswordRules::MinSize->get()),
                    new QtyLetters(PasswordRules::QtyLetters->get()),
                    new QtyUppercase(PasswordRules::QtyUppercase->get()),
                    new QtyLowercase(PasswordRules::QtyLowercase->get()),
                    new QtyDigits(PasswordRules::QtyDigits->get()),
                    new QtySpecialChars(PasswordRules::QtySpecialChars->get())
                )
            ]
        ];
    }

    public function messages(): array
    {
        return [
            'token.required' => Phrase::pickSentence(PhraseKey::ParameterRequired),

            'email.required' => Phrase::pickSentence(PhraseKey::ParameterRequired),
            'email.email' => Phrase::pickSentence(PhraseKey::EmailInvalid),

            'password.required' => Phrase::pickSentence(PhraseKey::ParameterRequired),
            'password.confirmed' => Phrase::pickSentence(PhraseKey::PassConfirmInvalid),
        ];
    }
}

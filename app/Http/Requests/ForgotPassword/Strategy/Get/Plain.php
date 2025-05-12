<?php

declare(strict_types=1);

namespace App\Http\Requests\ForgotPassword\Strategy\Get;

use App\Http\Requests\Checker;
use App\Library\Builders\Phrase;
use App\Library\Enums\PhraseKey;
use App\Rules\UserNoProvider;
use Illuminate\Foundation\Http\FormRequest;

final class Plain implements Checker
{
    public function all(FormRequest $formRequest, array $requestInputs): array
    {
        return [
            ...$requestInputs,
            'email' => $formRequest->query('email'),
        ];
    }

    public function rules(): array
    {
        return [
            'email' => [
                'required',
                'email',
                'exists:users,email',
                new UserNoProvider()
            ]
        ];
    }

    public function messages(): array
    {
        return [
            'email.required' => Phrase::pickSentence(PhraseKey::ParameterRequired),
            'email.email' => Phrase::pickSentence(PhraseKey::EmailInvalid),
            'email.exists' => Phrase::pickSentence(PhraseKey::ParameterInvalid),
        ];
    }
}

<?php

namespace App\Http\Requests\Auth\Strategy\Post;

use App\Http\Requests\Checker;
use App\Library\Builders\Phrase;
use App\Library\Enums\PhraseKey;
use App\Rules\UserNoProvider;
use Illuminate\Foundation\Http\FormRequest;

class Login implements Checker
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
            'email' => [
                'required',
                'email',
                new UserNoProvider()
            ],
            'password' => 'required',
        ];
    }

    public function messages(): array
    {
        return [
            'email.required' => Phrase::pickSentence(PhraseKey::ParameterRequired),
            'email.email' =>  Phrase::pickSentence(PhraseKey::EmailInvalid),
            'password.required' => Phrase::pickSentence(PhraseKey::ParameterRequired),
        ];
    }
}

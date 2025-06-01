<?php

namespace App\Http\Requests\User\Strategy\Post;

use App\Http\Requests\Checker;
use App\Library\Builders\Phrase;
use App\Library\Enums\PhraseKey;
use Illuminate\Foundation\Http\FormRequest;

final class Restore implements Checker
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
            'id' => [
                'required',
                'integer',
                'exists:users,id',
            ]
        ];
    }

    public function messages(): array
    {
        return [
            'id.required' => Phrase::pickSentence(PhraseKey::ParameterRequired),
            'id.integer' => Phrase::pickSentence(PhraseKey::ParameterInvalid),
            'id.exists' => Phrase::pickSentence(PhraseKey::ParameterInvalid),
        ];
    }
}

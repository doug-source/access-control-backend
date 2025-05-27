<?php

declare(strict_types=1);

namespace App\Http\Requests\Shared\Strategies;

use App\Library\Builders\Phrase;
use App\Library\Enums\PhraseKey;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class OwnerGet extends Get
{
    public function all(FormRequest $formRequest, array $requestInputs): array
    {
        return [
            ...parent::all($formRequest, $requestInputs),
            'owner' => $formRequest->query('owner'),
        ];
    }

    public function rules(): array
    {
        return [
            ...parent::rules(),
            'owner' => [
                'nullable',
                Rule::in(['yes', 'no']),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            ...parent::messages(),
            'owner.in' => Phrase::pickSentence(PhraseKey::ParameterInvalid),
        ];
    }
}

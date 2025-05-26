<?php

declare(strict_types=1);

namespace App\Http\Requests\AbilityRole\Strategy\Get;

use App\Http\Requests\Checker;
use App\Library\Builders\Phrase;
use App\Library\Enums\PhraseKey;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class Plain implements Checker
{
    public function all(FormRequest $formRequest, array $requestInputs): array
    {
        return [
            ...$requestInputs,
            'page' => $formRequest->query('page'),
            'group' => $formRequest->query('group'),
            'owner' => $formRequest->query('owner'),
        ];
    }

    public function rules(): array
    {
        return [
            'page' => 'nullable|integer|min:1',
            'group' => 'nullable|integer|min:1',
            'owner' => [
                'nullable',
                Rule::in(['yes', 'no']),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'page.integer' => Phrase::pickSentence(PhraseKey::ParameterInvalid),
            'page.min' => Phrase::pickSentence(PhraseKey::MinSizeInvalid, " (1)"),
            'group.integer' => Phrase::pickSentence(PhraseKey::ParameterInvalid),
            'group.min' => Phrase::pickSentence(PhraseKey::MinSizeInvalid, " (1)"),
            'owner.in' => Phrase::pickSentence(PhraseKey::ParameterInvalid),
        ];
    }
}

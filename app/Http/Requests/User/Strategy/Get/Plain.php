<?php

declare(strict_types=1);

namespace App\Http\Requests\User\Strategy\Get;

use App\Http\Requests\Checker;
use App\Library\Builders\Phrase;
use App\Library\Enums\PhraseKey;
use Illuminate\Foundation\Http\FormRequest;

final class Plain implements Checker
{
    public function all(FormRequest $formRequest, array $requestInputs): array
    {
        return [
            ...$requestInputs,
            'page' => $formRequest->query('page'),
            'group' => $formRequest->query('group'),
        ];
    }

    public function rules(): array
    {
        return [
            'page' => 'nullable|integer|min:1',
            'group' => 'nullable|integer|min:1',
        ];
    }

    public function messages(): array
    {
        return [
            'page.integer' => Phrase::pickSentence(PhraseKey::ParameterInvalid),
            'page.min' => Phrase::pickSentence(PhraseKey::MinSizeInvalid, " (1)"),
            'group.integer' => Phrase::pickSentence(PhraseKey::ParameterInvalid),
            'group.min' => Phrase::pickSentence(PhraseKey::MinSizeInvalid, " (1)"),
        ];
    }
}

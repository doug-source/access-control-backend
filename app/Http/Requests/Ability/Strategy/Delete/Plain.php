<?php

declare(strict_types=1);

namespace App\Http\Requests\Ability\Strategy\Delete;

use App\Http\Requests\Checker;
use App\Library\Builders\Phrase;
use App\Library\Enums\PhraseKey;
use App\Rules\MultipleExists;
use Illuminate\Foundation\Http\FormRequest;

final class Plain implements Checker
{
    public function all(FormRequest $formRequest, array $requestInputs): array
    {
        return [
            ...$requestInputs,
            'ability' => $formRequest->route('ability')->id
        ];
    }

    public function rules(): array
    {
        return [
            'ability' => [
                new MultipleExists(
                    references: [
                        [
                            'table' => 'ability_role',
                            'column' => 'ability_id',
                            'msg' => Phrase::pickSentence(PhraseKey::LinkNotAllowed, " (Role)")
                        ],
                        [
                            'table' => 'ability_user',
                            'column' => 'ability_id',
                            'msg' => Phrase::pickSentence(PhraseKey::LinkNotAllowed, " (User)")
                        ],
                    ],
                    positive: FALSE
                )
            ]
        ];
    }

    public function messages(): array
    {
        return [];
    }
}

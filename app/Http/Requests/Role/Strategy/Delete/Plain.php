<?php

declare(strict_types=1);

namespace App\Http\Requests\Role\Strategy\Delete;

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
            'role' => $formRequest->route('role')->id
        ];
    }

    public function rules(): array
    {
        return [
            'role' => [
                new MultipleExists(
                    references: [
                        [
                            'table' => 'ability_role',
                            'column' => 'role_id',
                            'msg' => Phrase::pickSentence(PhraseKey::LinkNotAllowed, " (Ability)")
                        ],
                        [
                            'table' => 'role_user',
                            'column' => 'role_id',
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

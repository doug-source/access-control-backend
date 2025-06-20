<?php

declare(strict_types=1);

namespace App\Http\Requests\AbilityRole\Strategy\Patch;

use App\Http\Requests\Checker;
use App\Library\Builders\Phrase;
use App\Library\Enums\PhraseKey;
use App\Models\Role;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class Plain implements Checker
{
    private ?Role $role = NULL;

    public function __construct(FormRequest $formRequest)
    {
        $this->role = $formRequest->input('role');
    }

    public function all(FormRequest $formRequest, array $requestInputs): array
    {
        return [
            ...$requestInputs,
        ];
    }

    public function rules(): array
    {
        $abilitiesFromRole = collect($this->role->abilities->pluck('name'));

        return [
            'removed' => [
                'nullable',
                'array',
            ],
            'removed.*' => [
                'bail',
                'exists:abilities,name',
                Rule::when($abilitiesFromRole->isEmpty(), 'prohibited'),
                Rule::when($abilitiesFromRole->isNotEmpty(), Rule::in(
                    $abilitiesFromRole->all()
                )),
            ],
            'included' => [
                'nullable',
                'array',
            ],
            'included.*' => [
                'bail',
                'exists:abilities,name',
                Rule::when($abilitiesFromRole->isNotEmpty(), Rule::notIn(
                    $abilitiesFromRole->all()
                )),
            ]
        ];
    }

    public function messages(): array
    {
        return [
            'removed.array' => Phrase::pickSentence(PhraseKey::ParameterInvalid),
            'removed.*.exists' => Phrase::pickSentence(PhraseKey::ParameterInvalid),
            'removed.*.prohibited' => Phrase::pickSentence(PhraseKey::InvalidRoleRemotion),
            'removed.*.in' => Phrase::pickSentence(PhraseKey::InvalidRoleRemotion),

            'included.array' => Phrase::pickSentence(PhraseKey::ParameterInvalid),
            'included.*.exists' => Phrase::pickSentence(PhraseKey::ParameterInvalid),
            'included.*.not_in' => Phrase::pickSentence(PhraseKey::InvalidRoleInclusion),
        ];
    }
}

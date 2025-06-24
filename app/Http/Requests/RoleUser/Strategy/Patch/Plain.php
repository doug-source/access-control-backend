<?php

declare(strict_types=1);

namespace App\Http\Requests\RoleUser\Strategy\Patch;

use App\Http\Requests\Checker;
use App\Library\Builders\Phrase;
use App\Library\Enums\PhraseKey;
use Illuminate\Support\Collection;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class Plain implements Checker
{
    private Collection $rolesFromUser;

    public function __construct(FormRequest $formRequest)
    {
        $this->rolesFromUser = $formRequest->input('rolesFromUser');
    }

    public function all(FormRequest $formRequest, array $requestInputs): array
    {
        return [
            ...$requestInputs,
        ];
    }

    public function rules(): array
    {
        $rolesFromUser = collect($this->rolesFromUser->pluck('name'));

        return [
            'removed' => [
                'nullable',
                'array',
            ],
            'removed.*' => [
                'bail',
                'exists:roles,name',
                Rule::when($rolesFromUser->isEmpty(), 'prohibited'),
                Rule::when($rolesFromUser->isNotEmpty(), Rule::in(
                    $rolesFromUser->all()
                )),
            ],
            'included' => [
                'nullable',
                'array',
            ],
            'included.*' => [
                'bail',
                'exists:roles,name',
                Rule::when($rolesFromUser->isNotEmpty(), Rule::notIn(
                    $rolesFromUser->all()
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

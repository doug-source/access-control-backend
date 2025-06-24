<?php

declare(strict_types=1);

namespace App\Http\Requests\AbilityUser\Strategy\Patch;

use App\Http\Requests\Checker;
use App\Library\Builders\Phrase;
use App\Library\Enums\PhraseKey;
use App\Models\Ability;
use App\Models\User;
use App\Services\Ability\Contracts\AbilityServiceInterface;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;

class Plain implements Checker
{
    /** @var \Illuminate\Support\Collection<int, string> */
    private Collection $includedInput;
    /** @var \Illuminate\Support\Collection<int, string> */
    private Collection $removedInput;

    private ?User $user = NULL;
    /** @var array{included: \Illuminate\Support\Collection<int, \App\Models\Ability>, removed:\Illuminate\Support\Collection<int, \App\Models\Ability>} */
    private array $singleAbilities;

    private AbilityServiceInterface $abilityService;

    public function __construct(FormRequest $formRequest)
    {
        $this->user = $formRequest->input('user');
        $this->singleAbilities = $formRequest->input('singleAbilities');
        $this->abilityService = app(AbilityServiceInterface::class);
        $this->includedInput = collect($formRequest->input('included'));
        $this->removedInput = collect($formRequest->input('removed'));
    }

    public function all(FormRequest $formRequest, array $requestInputs): array
    {
        return [
            ...$requestInputs,
        ];
    }

    public function rules(): array
    {
        $abilitiesFromRole = $this->abilityService->abilitiesFromUserRoles($this->user);
        ['included' => $includeList, 'removed' => $removeList] = $this->singleAbilities;

        return [
            'removed' => [
                'nullable',
                'array',
            ],
            'removed.*' => [
                'bail',
                'exists:abilities,name',
                Rule::when(
                    $removeList->isNotEmpty(),
                    Rule::notIn($removeList->pluck('name')->all())
                ),
                Rule::when(
                    $abilitiesFromRole->isEmpty() && $includeList->isEmpty(),
                    'prohibited'
                ),
                Rule::when(
                    $abilitiesFromRole->isEmpty() && $includeList->isNotEmpty(),
                    Rule::in($includeList->pluck('name')->all())
                ),
                Rule::when(
                    $abilitiesFromRole->isNotEmpty() && $includeList->isEmpty(),
                    Rule::in($abilitiesFromRole->pluck('name')->all())
                ),
                Rule::when(
                    $abilitiesFromRole->isNotEmpty() && $includeList->isNotEmpty(),
                    Rule::in(
                        $abilitiesFromRole->merge($includeList)->pluck('name')->all()
                    )
                ),
                Rule::when(
                    $this->includedInput->isNotEmpty(),
                    Rule::notIn($this->includedInput->all())
                ),
            ],
            'included' => [
                'nullable',
                'array',
            ],
            'included.*' => [
                'bail',
                'exists:abilities,name',
                Rule::when(
                    $includeList->isNotEmpty(),
                    Rule::notIn($includeList->pluck('name')->all())
                ),
                Rule::when(
                    $abilitiesFromRole->isNotEmpty() && $removeList->isEmpty(),
                    Rule::notIn($abilitiesFromRole->pluck('name')->all())
                ),
                Rule::when(
                    $abilitiesFromRole->isNotEmpty() && $removeList->isNotEmpty(),
                    Rule::notIn($abilitiesFromRole->filter(
                        fn(Ability $ability) => $this->includedInput->contains(
                            fn(string $name) => $ability->name === $name
                        )
                    )->reject(
                        fn(Ability $ability) => $removeList->contains(
                            fn(Ability $removed) => $removed->name === $ability->name
                        )
                    )->pluck('name')->all())
                ),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'removed.array' => Phrase::pickSentence(PhraseKey::ParameterInvalid),
            'removed.*.exists' => Phrase::pickSentence(PhraseKey::ParameterInvalid),
            'removed.*.prohibited' => Phrase::pickSentence(PhraseKey::InvalidAbilityRemotion),
            'removed.*.not_in' => Phrase::pickSentence(PhraseKey::InvalidAbilityRemotion),
            'removed.*.in' => Phrase::pickSentence(PhraseKey::InvalidAbilityRemotion),

            'included.array' => Phrase::pickSentence(PhraseKey::ParameterInvalid),
            'included.*.exists' => Phrase::pickSentence(PhraseKey::ParameterInvalid),
            'included.*.not_in' => Phrase::pickSentence(PhraseKey::InvalidAbilityInclusion),
        ];
    }
}

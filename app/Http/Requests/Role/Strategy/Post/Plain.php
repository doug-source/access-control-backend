<?php

declare(strict_types=1);

namespace App\Http\Requests\Role\Strategy\Post;

use App\Http\Requests\Checker;
use App\Library\Builders\Phrase;
use App\Library\Enums\ColumnSize\RoleSize;
use App\Library\Enums\PhraseKey;
use Illuminate\Foundation\Http\FormRequest;

final class Plain implements Checker
{
    private int $maxColumnSize;

    public function __construct()
    {
        $this->maxColumnSize = RoleSize::NAME->get();
    }

    public function all(FormRequest $formRequest, array $requestInputs): array
    {
        return [
            ...$requestInputs,
        ];
    }

    public function rules(): array
    {
        return [
            'name' => [
                'bail',
                'required',
                'unique:App\Models\Role,name',
                "max:{$this->maxColumnSize}"
            ]
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => Phrase::pickSentence(PhraseKey::ParameterRequired),
            'name.unique' => Phrase::pickSentence(PhraseKey::NameAlreadyUsed),
            'name.max' => Phrase::pickSentence(PhraseKey::MaxSizeInvalid, " ({$this->maxColumnSize})"),
        ];
    }
}

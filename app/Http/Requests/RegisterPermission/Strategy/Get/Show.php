<?php

declare(strict_types=1);

namespace App\Http\Requests\RegisterPermission\Strategy\Get;

use App\Http\Requests\Checker;
use App\Library\Builders\Phrase;
use App\Library\Enums\PhraseKey;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Used by RegisterPermission show
 */
final class Show implements Checker
{
    public function all(FormRequest $formRequest, array $requestInputs): array
    {
        return [
            ...$requestInputs,
            'registerPermissionID' => $formRequest->route('registerPermissionID'),
        ];
    }

    public function rules(): array
    {
        return [
            'registerPermissionID' => [
                'bail',
                'integer',
                'min:1',
                'exists:register_permissions,id',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'registerPermissionID.integer' => Phrase::pickSentence(PhraseKey::ParameterInvalid),
            'registerPermissionID.min' => Phrase::pickSentence(PhraseKey::ParameterInvalid),
            'registerPermissionID.exists' => Phrase::pickSentence(PhraseKey::ParameterInvalid),
        ];
    }
}

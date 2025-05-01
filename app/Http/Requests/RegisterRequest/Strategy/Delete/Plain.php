<?php

declare(strict_types=1);

namespace App\Http\Requests\RegisterRequest\Strategy\Delete;

use App\Http\Requests\Checker;
use App\Library\Builders\Phrase;
use App\Library\Enums\PhraseKey;
use Illuminate\Foundation\Http\FormRequest;

class Plain implements Checker
{
    public function all(FormRequest $formRequest, array $requestInputs): array
    {
        return [
            ...$requestInputs,
            'registerRequestID' => $formRequest->route('registerRequestID'),
        ];
    }

    public function rules(): array
    {
        return [
            'registerRequestID' => [
                'bail',
                'integer',
                'min:1',
                'exists:register_requests,id',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'registerRequestID.integer' => Phrase::pickSentence(PhraseKey::ParameterInvalid),
            'registerRequestID.min' => Phrase::pickSentence(PhraseKey::ParameterInvalid),
            'registerRequestID.exists' => Phrase::pickSentence(PhraseKey::ParameterInvalid),
        ];
    }
}

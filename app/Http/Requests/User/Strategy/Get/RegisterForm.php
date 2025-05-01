<?php

declare(strict_types=1);

namespace App\Http\Requests\User\Strategy\Get;

use App\Http\Requests\Checker;
use App\Library\Builders\Phrase;
use App\Library\Enums\PhraseKey;
use App\Library\Enums\RegisterPermissionColumnSize;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Used by User Create Form process.
 * @see App\Http\Controllers\UserController::create()
 */
class RegisterForm implements Checker
{
    private int $tokenColumnSize;

    public function __construct()
    {
        $this->tokenColumnSize = RegisterPermissionColumnSize::TOKEN->get();
    }

    public function all(FormRequest $formRequest, array $requestInputs): array
    {
        return [
            ...$requestInputs,
            'token' => $formRequest->query('token'),
        ];
    }

    public function rules(): array
    {
        return [
            'token' => [
                'required',
                "max:{$this->tokenColumnSize}",
                'exists:register_permissions,token'
            ]
        ];
    }

    public function messages(): array
    {
        return [
            'token.required' => Phrase::pickSentence(PhraseKey::ParameterRequired),
            'token.max' => Phrase::pickSentence(PhraseKey::MaxSizeInvalid, " ({$this->tokenColumnSize})"),
            'token.exists' => Phrase::pickSentence(PhraseKey::ParameterInvalid)
        ];
    }
}

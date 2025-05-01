<?php

namespace App\Http\Requests\Auth\Strategy\Post;

use App\Http\Requests\Checker;
use App\Library\Builders\Phrase;
use App\Library\Enums\PhraseKey;
use App\Rules\UserNotProvided;
use Illuminate\Foundation\Http\FormRequest;

class Login implements Checker
{
    /** @var string */
    private $email;

    public function __construct(FormRequest $formRequest)
    {
        $this->email = $formRequest->input('email');
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
            'email' => [
                'required',
                'email',
                new UserNotProvided($this->email)
            ],
            'password' => 'required',
        ];
    }

    public function messages(): array
    {
        return [
            'email.required' => Phrase::pickSentence(PhraseKey::ParameterRequired),
            'email.email' =>  Phrase::pickSentence(PhraseKey::EmailInvalid),
            'password.required' => Phrase::pickSentence(PhraseKey::ParameterRequired),
        ];
    }
}

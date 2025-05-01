<?php

namespace App\Http\Requests\User\Strategy\Post;

use App\Http\Requests\Checker;
use App\Library\Builders\Phrase;
use App\Library\Chains\Password\Handlers\MinSize;
use App\Library\Chains\Password\Handlers\QtyDigits;
use App\Library\Chains\Password\Handlers\QtyLetters;
use App\Library\Chains\Password\Handlers\QtyLowercase;
use App\Library\Chains\Password\Handlers\QtySpecialChars;
use App\Library\Chains\Password\Handlers\QtyUppercase;
use App\Library\Enums\PasswordRules;
use App\Library\Enums\PhraseKey;
use App\Library\Enums\RegisterPermissionColumnSize;
use App\Library\Enums\UserColumnSize;
use App\Rules\PasswordValid;
use App\Rules\PhoneValid;
use App\Rules\RegisterPermissionValid;
use Illuminate\Foundation\Http\FormRequest;

final class Plain implements Checker
{
    private int $nameMaxSize;
    private int $emailMaxSize;
    private int $phoneMaxSize;
    private int $tokenMaxSize;

    private ?string $email = NULL;
    private ?string $token = NULL;

    public function __construct(FormRequest $formRequest)
    {
        $this->nameMaxSize = UserColumnSize::NAME->get();
        $this->emailMaxSize = UserColumnSize::EMAIL->get();
        $this->phoneMaxSize = UserColumnSize::PHONE->get();
        $this->tokenMaxSize = RegisterPermissionColumnSize::TOKEN->get();

        $this->email = $formRequest->input('email');
        $this->token = $formRequest->query('token');
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
                'required',
                "max:{$this->nameMaxSize}"
            ],
            'email' => [
                'required',
                'email',
                "max:{$this->emailMaxSize}",
                'unique:App\Models\User,email',
                'exists:register_permissions,email'
            ],
            'phone' => [
                'nullable',
                new PhoneValid($this->phoneMaxSize)
            ],
            'password' => [
                'bail',
                'required',
                'confirmed',
                new PasswordValid(
                    new MinSize(PasswordRules::MinSize->get()),
                    new QtyLetters(PasswordRules::QtyLetters->get()),
                    new QtyUppercase(PasswordRules::QtyUppercase->get()),
                    new QtyLowercase(PasswordRules::QtyLowercase->get()),
                    new QtyDigits(PasswordRules::QtyDigits->get()),
                    new QtySpecialChars(PasswordRules::QtySpecialChars->get())
                )
            ],
            'token' => [
                'bail',
                'required',
                "max:{$this->tokenMaxSize}",
                new RegisterPermissionValid($this->email, $this->token)
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => Phrase::pickSentence(PhraseKey::ParameterRequired),
            'name.max' => Phrase::pickSentence(PhraseKey::MaxSizeInvalid, " ({$this->nameMaxSize})"),
            'email.required' => Phrase::pickSentence(PhraseKey::ParameterRequired),
            'email.email' => Phrase::pickSentence(PhraseKey::EmailInvalid),
            'email.max' => Phrase::pickSentence(PhraseKey::MaxSizeInvalid, " ({$this->emailMaxSize})"),
            'email.unique' => Phrase::pickSentence(PhraseKey::EmailInvalid),
            'email.exists' => Phrase::pickSentence(PhraseKey::EmailInvalid),
            'password.required' => Phrase::pickSentence(PhraseKey::ParameterRequired),
            'password.confirmed' => Phrase::pickSentence(PhraseKey::PassConfirmInvalid),
            'token.required' => Phrase::pickSentence(PhraseKey::ParameterRequired),
            'token.max' => Phrase::pickSentence(PhraseKey::MaxSizeInvalid, " ({$this->tokenMaxSize})"),
        ];
    }
}

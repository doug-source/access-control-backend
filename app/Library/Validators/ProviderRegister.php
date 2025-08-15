<?php

declare(strict_types=1);

namespace App\Library\Validators;

use App\Library\Builders\Phrase;
use App\Library\Enums\PhraseKey;
use App\Library\Enums\ColumnSize\RegisterPermissionSize;
use App\Repositories\RegisterPermissionRepository;
use App\Rules\RegisterPermissionValid;

final class ProviderRegister extends AbstractProvider
{
    protected string $email;
    protected ?string $token;
    protected int $tokenMaxSize;

    public function __construct($provider, string $email, ?string $token = NULL)
    {
        parent::__construct($provider);
        $this->email = $email;
        $this->token = $token;
        $this->tokenMaxSize = RegisterPermissionSize::TOKEN->get();
    }

    protected function fields(): array
    {
        $fields = parent::fields();
        return [
            ...$fields,
            'email' => $this->email,
            'token' => $this->token,
        ];
    }

    protected function rules(): array
    {
        $rules = parent::rules();
        return [
            ...$rules,
            'email' => ['required', 'unique:App\Models\User,email'],
            'token' => [
                'bail',
                'required',
                "max:{$this->tokenMaxSize}",
                new RegisterPermissionValid(
                    allowed: app(RegisterPermissionRepository::class)->findByEmail($this->email),
                    token: $this->token,
                )
            ]
        ];
    }

    protected function messages(): array
    {
        $messages = parent::messages();
        return [
            ...$messages,
            'email.required' => Phrase::pickSentence(PhraseKey::ParameterRequired),
            'email.unique' => Phrase::pickSentence(PhraseKey::EmailInvalid),
            'token.required' => Phrase::pickSentence(PhraseKey::ParameterRequired),
            'token.max' => Phrase::pickSentence(PhraseKey::MaxSizeInvalid, " ({$this->tokenMaxSize})"),
        ];
    }
}

<?php

namespace App\Library\Validators;

use App\Library\Builders\Phrase;
use App\Library\Enums\ColumnSize\RegisterPermissionSize;
use App\Library\Enums\PhraseKey;
use App\Library\Validators\Contracts\CustomValidatorInterface;
use App\Repositories\RegisterPermissionRepository;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use App\Rules\RegisterPermissionValid;

final class ProviderRegister implements CustomValidatorInterface
{
    /** @var string */
    protected $provider;

    protected string $email;

    protected ?string $token;

    protected string $tokenMaxSize;

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($provider, string $email, ?string $token = NULL)
    {
        $this->provider = $provider;
        $this->email = $email;
        $this->token = $token;
        $this->tokenMaxSize = RegisterPermissionSize::TOKEN->get();
    }

    /**
     * Execute the validation
     * @return array{url: string}|array
     */
    public function validate(): array
    {
        $providers = config('services.providers');
        $validator = Validator::make([
            'provider' => $this->provider,
            'email' => $this->email,
            'token' => $this->token,
        ], [
            'provider' => [
                Rule::in($providers),
            ],
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
        ], [
            'provider.in' => Phrase::pickSentence(PhraseKey::ProviderInvalid),
            'email.required' => Phrase::pickSentence(PhraseKey::ParameterRequired),
            'email.unique' => Phrase::pickSentence(PhraseKey::EmailInvalid),
            'token.required' => Phrase::pickSentence(PhraseKey::ParameterRequired),
            'token.max' => Phrase::pickSentence(PhraseKey::MaxSizeInvalid, " ({$this->tokenMaxSize})"),
        ]);

        if ($validator->stopOnFirstFailure()->fails()) {
            return [
                $validator->errors()->first()
            ];
        }
        return [];
    }
}

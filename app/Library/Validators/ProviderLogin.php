<?php

namespace App\Library\Validators;

use App\Library\Builders\Phrase;
use App\Library\Enums\PhraseKey;
use App\Library\Validators\Contracts\CustomValidatorInterface;
use App\Repositories\UserRepository;
use App\Rules\ProviderUserLinked;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

final class ProviderLogin implements CustomValidatorInterface
{
    /** @var string */
    protected $provider;

    protected UserRepository $userRepository;

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($provider, UserRepository $userRepository)
    {
        $this->provider = $provider;
        $this->userRepository = $userRepository;
    }

    /**
     * Execute the validation
     * @return array{url: string}|array
     */
    public function validate(): array
    {
        $providers = config('services.providers');
        $validator = Validator::make(['provider' => $this->provider], [
            'provider' => [
                Rule::in($providers),
                new ProviderUserLinked(
                    provider: $this->provider,
                    userRepository: $this->userRepository
                )
            ]
        ], [
            'provider.in' => Phrase::pickSentence(PhraseKey::ProviderInvalid)
        ]);

        if ($validator->stopOnFirstFailure()->fails()) {
            return [
                $validator->errors()->first()
            ];
        }
        return [];
    }
}

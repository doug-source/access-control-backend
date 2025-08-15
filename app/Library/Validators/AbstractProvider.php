<?php

declare(strict_types=1);

namespace App\Library\Validators;

use Illuminate\Validation\Rule;
use App\Library\Builders\Phrase;
use App\Library\Enums\PhraseKey;
use Illuminate\Support\Facades\Validator;
use App\Library\Validators\Contracts\CustomValidatorInterface;

abstract class AbstractProvider implements CustomValidatorInterface
{
    /** @var string */
    protected $provider;

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($provider)
    {
        $this->provider = $provider;
    }

    /**
     * Define fields to be be validated
     */
    protected function fields(): array
    {
        return ['provider' => $this->provider];
    }

    /**
     * Define rules to validate fields
     */
    protected function rules(): array
    {
        $providers = config('services.providers');
        return [
            'provider' => [
                Rule::in($providers),
            ]
        ];
    }

    /**
     * Define messages to response rules' fail validation
     */
    protected function messages(): array
    {
        return [
            'provider.in' => Phrase::pickSentence(PhraseKey::ProviderInvalid)
        ];
    }

    /**
     * Execute the validation
     * @return array{url: string}|array
     */
    public function validate(): array
    {
        $validator = Validator::make($this->fields(), $this->rules(), $this->messages());

        if ($validator->stopOnFirstFailure()->fails()) {
            return [
                $validator->errors()->first()
            ];
        }
        return [];
    }
}

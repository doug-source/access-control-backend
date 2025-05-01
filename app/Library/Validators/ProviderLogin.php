<?php

namespace App\Library\Validators;

use App\Library\Builders\Phrase;
use App\Library\Enums\PhraseKey;
use App\Rules\ProviderUserLinked;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

final class ProviderLogin
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
     * Execute the validation
     * @return array{url: string}|array
     */
    public function validate()
    {
        $providers = config('services.providers');
        $validator = Validator::make(['provider' => $this->provider], [
            'provider' => [
                Rule::in($providers),
                new ProviderUserLinked($this->provider)
            ]
        ], [
            'provider.in' => Phrase::pickSentence(PhraseKey::ProviderInvalid)
        ]);

        if ($validator->stopOnFirstFailure()->fails()) {
            $qs = http_build_query([
                'errormsg' => $validator->errors()->first()
            ]);
            $frontendUrl = config('app.frontend.uri.host');
            return [
                'url' => "{$frontendUrl}?{$qs}"
            ];
        }
        return [];
    }
}

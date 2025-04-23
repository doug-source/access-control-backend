<?php

namespace App\Library\Validators;

use App\Rules\ProviderUserLinked;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

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
            'provider.in' => Str::of(__('provider-invalid', [
                'invalid' => __('invalid')
            ]))->ucfirst()
        ]);

        if ($validator->stopOnFirstFailure()->fails()) {
            $qs = http_build_query([
                'errormsg' => $validator->errors()->first()
            ]);
            $frontendUrl = config('app.frontend-url');
            return [
                'url' => "{$frontendUrl}?{$qs}"
            ];
        }
        return [];
    }
}

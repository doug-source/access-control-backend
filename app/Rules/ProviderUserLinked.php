<?php

namespace App\Rules;

use App\Models\User;
use Closure;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Contracts\Validation\ValidationRule;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Str;

class ProviderUserLinked implements ValidationRule
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
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        try {
            $providerUser = Socialite::driver($this->provider)->user();
            $user = User::where('email', $providerUser->getEmail())->first();

            if (is_null($user)) {
                $fail($this->pickErrorMessage('user-nullable'));
            } else if (!is_null($user->password)) {
                $fail($this->pickErrorMessage('password-not-nullable'));
            }
        } catch (ClientException $th) {
            $fail($this->pickErrorMessage('invalid-provider'));
        }
    }

    /**
     * Determine the error message from this validator
     */
    protected function pickErrorMessage(string $key)
    {
        return match ($key) {
            'user-nullable' => Str::of(__('register-required', [
                'register' => __('register'),
                'required' => __('required')
            ]))->ucfirst(),
            'password-not-nullable' => Str::of(__('login-with-password-required', [
                'log-in' => __('log-in'),
                'with' => __('with'),
                'password' => __('password'),
                'required' => __('required'),
            ]))->ucfirst(),
            'invalid-provider' => Str::of(__('provider-invalid', [
                'invalid' => __('invalid')
            ]))->ucfirst(),
            default => false
        };
    }
}

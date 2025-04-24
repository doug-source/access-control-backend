<?php

declare(strict_types=1);

namespace App\Library\Builders;

use App\Library\Enums\PhraseKey;
use Illuminate\Support\Str;

final class Phrase
{
    /**
     * Determine the application's multiple sentences
     */
    public static function pickSentence(PhraseKey $key)
    {
        $providers = config('services.providers');

        return match ($key) {
            PhraseKey::UserNullable => Str::of(__('register-required', [
                'register' => __('register'),
                'required' => __('required-m')
            ]))->ucfirst(),
            PhraseKey::PasswordNotNullable => Str::of(__('login-with-password-required', [
                'log-in' => __('log-in'),
                'with' => __('with'),
                'password' => __('password'),
                'required' => __('required-m'),
            ]))->ucfirst(),
            PhraseKey::ProviderInvalid => Str::of(__('provider-invalid', [
                'provider' => __('provider'),
                'invalid' => __('invalid-m')
            ]))->ucfirst(),
            PhraseKey::LoginByProvider => Str::of(__('login-by-provider', [
                'log-in' => __('log-in'),
                'with' => __('with'),
                'provider' => implode(' ' . _('or') . ' ', $providers),
                'required' => __('required-m')
            ]))->ucfirst(),
            PhraseKey::EmailRequired => Str::of(__('email-required'), [
                'email' => __('email'),
                'required' => __('required-m')
            ])->ucfirst(),
            PhraseKey::EmailInvalid => Str::of(__('email-invalid', [
                'email' => __('email'),
                'invalid' => __('invalid-m')
            ]))->ucfirst(),
            PhraseKey::PasswordRequired => __('password-required', [
                'password' => __('password'),
                'required' => __('required-f')
            ]),
            PhraseKey::LoginInvalid => __('login-invalid', [
                'login-in' => 'log-in',
                'invalid' => 'invalid-m'
            ]),
            default => false
        };
    }
}

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
            PhraseKey::ParameterRequired => Str::of(__('parameter-required', [
                'parameter' => __('parameter'),
                'required' => __('required-m')
            ]))->ucfirst(),
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
            PhraseKey::PasswordRequired => Str::of(__('password-required', [
                'password' => __('password'),
                'required' => __('required-f')
            ]))->ucfirst(),
            PhraseKey::LoginInvalid => Str::of(__('login-invalid', [
                'login-in' => 'log-in',
                'invalid' => 'invalid-m'
            ]))->ucfirst(),
            PhraseKey::ParameterInvalid => Str::of(__('parameter-invalid', [
                'parameter' => __('parameter'),
                'invalid' => __('invalid-m')
            ]))->ucfirst(),
            PhraseKey::MinSizeInvalid => Str::of(__('min-size-invalid', [
                'minimum' => __('minimum'),
                'size' => __('size'),
            ]))->ucfirst(),
            PhraseKey::MaxSizeInvalid => Str::of(__('max-size-invalid', [
                'maximum' => __('maximum'),
                'size' => __('size'),
            ]))->ucfirst(),
            PhraseKey::Congratulations => Str::of(__('congratulations'))->ucfirst(),
            PhraseKey::ClickHere => Str::of(__('click-here'))->ucfirst(),
            PhraseKey::PreRegisterUserTextOne => Str::of(__('pre-register-user-text-1'))->ucfirst(),
            PhraseKey::PreRegisterUserTextTwo => Str::of(__('pre-register-user-text-2'))->ucfirst(),
            PhraseKey::Regards => Str::of(__('regards'))->ucfirst(),
            default => false
        };
    }
}

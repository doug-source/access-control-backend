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
    public static function pickSentence(PhraseKey $key, string $remain = '')
    {
        $providers = config('services.providers');

        $message = match ($key) {
            PhraseKey::ParameterRequired => Str::of(__('subject-required', [
                'subject' => __('parameter'),
                'required' => __('required-m')
            ]))->ucfirst(),
            PhraseKey::UserNullable => Str::of(__('subject-required', [
                'subject' => __('register'),
                'required' => __('required-m')
            ]))->ucfirst(),
            PhraseKey::PasswordNotNullable => Str::of(__('subject-required', [
                'subject' => __('login-with-password', [
                    'log-in' => __('log-in'),
                    'with' => __('with'),
                    'password' => __('password'),
                ]),
                'required' => __('required-m')
            ]))->ucfirst(),
            PhraseKey::ProviderInvalid => Str::of(__('subject-invalid', [
                'subject' => __('provider'),
                'invalid' => __('invalid-m')
            ]))->ucfirst(),
            PhraseKey::LoginByProvider => Str::of(__('login-by-provider', [
                'log-in' => __('log-in'),
                'with' => __('with'),
                'provider' => implode(' ' . _('or') . ' ', $providers),
                'required' => __('required-m')
            ]))->ucfirst(),
            PhraseKey::EmailInvalid => Str::of(__('subject-invalid', [
                'subject' => __('email'),
                'invalid' => __('invalid-m')
            ]))->ucfirst(),
            PhraseKey::LoginInvalid => Str::of(__('subject-invalid', [
                'subject' => __('log-in'),
                'invalid' => __('invalid-m')
            ]))->ucfirst(),
            PhraseKey::ParameterInvalid => Str::of(__('subject-invalid', [
                'subject' => __('parameter'),
                'invalid' => __('invalid-m')
            ]))->ucfirst(),
            PhraseKey::MinSizeInvalid => Str::of(__('min-size-invalid', [
                'minimum' => __('minimum-m'),
                'size' => __('size'),
            ]))->ucfirst(),
            PhraseKey::MaxSizeInvalid => Str::of(__('max-size-invalid', [
                'maximum' => __('maximum'),
                'size' => __('size'),
            ]))->ucfirst(),
            PhraseKey::MinLettersInvalid => Str::of(__('min-qty-invalid', [
                'minimum' => __('minimum-f'),
                'quantity' => __('quantity'),
                'subject' => __('letters')
            ]))->ucfirst(),
            PhraseKey::MinUppercaseInvalid => Str::of(__('min-qty-invalid', [
                'minimum' => __('minimum-f'),
                'quantity' => __('quantity'),
                'subject' => __('uppercases')
            ]))->ucfirst(),
            PhraseKey::MinLowercaseInvalid => Str::of(__('min-qty-invalid', [
                'minimum' => __('minimum-f'),
                'quantity' => __('quantity'),
                'subject' => __('lowercases')
            ]))->ucfirst(),
            PhraseKey::MinDigitsInvalid => Str::of(__('min-qty-invalid', [
                'minimum' => __('minimum-f'),
                'quantity' => __('quantity'),
                'subject' => __('digits')
            ]))->ucfirst(),
            PhraseKey::MinSpecialCharsInvalid => Str::of(__('min-qty-invalid', [
                'minimum' => __('minimum-f'),
                'quantity' => __('quantity'),
                'subject' => __('special-chars')
            ]))->ucfirst(),
            PhraseKey::RegistrationExpired => Str(__('registration-expired'))->ucfirst(),
            PhraseKey::PassConfirmInvalid => Str::of(__('pass-confirm-invalid', [
                'password' => __('password'),
                'confirmation' => __('confirmation')
            ]))->ucfirst(),
            PhraseKey::EmailAlreadyVerified => Str(__('verify-email-already-verified', [
                'email' => __('email'),
                'verified' => __('verified')
            ]))->ucfirst(),
            PhraseKey::Congratulations => Str::of(__('congratulations'))->ucfirst(),
            PhraseKey::ClickHere => Str::of(__('click-here'))->ucfirst(),
            PhraseKey::PreRegisterUserTextOne => Str::of(__('pre-register-user-text-1'))->ucfirst(),
            PhraseKey::PreRegisterUserTextTwo => Str::of(__('pre-register-user-text-2', [
                'button' => __('button')
            ]))->ucfirst(),
            PhraseKey::Regards => Str::of(__('regards'))->ucfirst(),
            PhraseKey::Hello => Str::of(__('hello'))->ucfirst(),
            PhraseKey::ConfirmationEmail => Str::of(__('confirmation-email', [
                'confirmation' => __('confirmation'),
                'email' => __('email')
            ]))->ucfirst(),
            PhraseKey::ConfirmationEmailText => Str::of(__('confirmation-email-text', [
                'button' => __('button'),
                'email-address' => __('email-address', [
                    'address' => __('address'),
                    'email' => __('email')
                ])
            ]))->ucfirst(),
            PhraseKey::RegisterApproval => Str::of(__('register-approval', [
                'register' => __('register')
            ]))->ucfirst(),
        };
        return $message->append($remain);
    }
}

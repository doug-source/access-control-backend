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
    public static function pickSentence(PhraseKey $key, string $remain = '', $uppercase = TRUE)
    {
        $providers = config('services.providers');

        $message = match ($key) {
            PhraseKey::Or => Str::of(__('or')),
            PhraseKey::ParameterRequired => Str::of(__('subject-required', [
                'subject' => __('parameter'),
                'required' => __('required-m')
            ])),
            PhraseKey::UserNullable => Str::of(__('subject-required', [
                'subject' => __('register'),
                'required' => __('required-m')
            ])),
            PhraseKey::PasswordNotNullable => Str::of(__('subject-required', [
                'subject' => __('login-with-password', [
                    'log-in' => __('log-in'),
                    'with' => __('with'),
                    'password' => __('password'),
                ]),
                'required' => __('required-m')
            ])),
            PhraseKey::ProviderInvalid => Str::of(__('subject-invalid', [
                'subject' => __('provider'),
                'invalid' => __('invalid-m')
            ])),
            PhraseKey::ProviderCredentialsInvalid => Str::of(__('provider-credentials-invalid', [
                'invalid' => __('invalid-f')
            ])),
            PhraseKey::LoginByProvider => Str::of(__('login-by-provider', [
                'log-in' => __('log-in'),
                'with' => __('with'),
                'provider' => implode(' ' . _('or') . ' ', $providers),
                'required' => __('required-m')
            ])),
            PhraseKey::EmailInvalid => Str::of(__('subject-invalid', [
                'subject' => __('email'),
                'invalid' => __('invalid-m')
            ])),
            PhraseKey::LoginInvalid => Str::of(__('subject-invalid', [
                'subject' => __('log-in'),
                'invalid' => __('invalid-m')
            ])),
            PhraseKey::ParameterInvalid => Str::of(__('subject-invalid', [
                'subject' => __('parameter'),
                'invalid' => __('invalid-m')
            ])),
            PhraseKey::MinSizeInvalid => Str::of(__('min-size-invalid', [
                'minimum' => __('minimum-m'),
                'size' => __('size'),
            ])),
            PhraseKey::MaxSizeInvalid => Str::of(__('max-size-invalid', [
                'maximum' => __('maximum'),
                'size' => __('size'),
            ])),
            PhraseKey::MinLettersInvalid => Str::of(__('min-qty-invalid', [
                'minimum' => __('minimum-f'),
                'quantity' => __('quantity'),
                'subject' => __('letters')
            ])),
            PhraseKey::MinUppercaseInvalid => Str::of(__('min-qty-invalid', [
                'minimum' => __('minimum-f'),
                'quantity' => __('quantity'),
                'subject' => __('uppercases')
            ])),
            PhraseKey::MinLowercaseInvalid => Str::of(__('min-qty-invalid', [
                'minimum' => __('minimum-f'),
                'quantity' => __('quantity'),
                'subject' => __('lowercases')
            ])),
            PhraseKey::MinDigitsInvalid => Str::of(__('min-qty-invalid', [
                'minimum' => __('minimum-f'),
                'quantity' => __('quantity'),
                'subject' => __('digits')
            ])),
            PhraseKey::MinSpecialCharsInvalid => Str::of(__('min-qty-invalid', [
                'minimum' => __('minimum-f'),
                'quantity' => __('quantity'),
                'subject' => __('special-chars')
            ])),
            PhraseKey::RegistrationExpired => Str(__('registration-expired')),
            PhraseKey::PassConfirmInvalid => Str::of(__('pass-confirm-invalid', [
                'password' => __('password'),
                'confirmation' => __('confirmation')
            ])),
            PhraseKey::EmailAlreadyVerified => Str(__('verify-email-already-verified', [
                'email' => __('email'),
                'verified' => __('verified')
            ])),
            PhraseKey::Congratulations => Str::of(__('congratulations')),
            PhraseKey::ClickHere => Str::of(__('click-here')),
            PhraseKey::PreRegisterUserTextOne => Str::of(__('pre-register-user-text-1')),
            PhraseKey::PreRegisterUserTextTwo => Str::of(__('pre-register-user-text-2', [
                'button' => __('button')
            ])),
            PhraseKey::Regards => Str::of(__('regards')),
            PhraseKey::Hello => Str::of(__('hello')),
            PhraseKey::ConfirmationEmail => Str::of(__('confirmation-email', [
                'confirmation' => __('confirmation'),
                'email' => __('email')
            ])),
            PhraseKey::ConfirmationEmailText => Str::of(__('confirmation-email-text', [
                'button' => __('button'),
                'email-address' => __('email-address', [
                    'address' => __('address'),
                    'email' => __('email')
                ])
            ])),
            PhraseKey::RegisterApproval => Str::of(__('register-approval', [
                'register' => __('register')
            ])),

            PhraseKey::PasswordsSent => Str::of(__('passwords.sent', [
                'email' => __('email'),
                'password' => __('password')
            ])),
            PhraseKey::PasswordsReset => Str::of(__('passwords.reset', [
                'password' => __('password')
            ])),
            PhraseKey::PasswordsThrottled => Str::of(__('passwords.throttled')),
            PhraseKey::PasswordsToken => Str::of(__('subject-invalid', [
                'subject' => __('password-reset-token', [
                    'password' => __('password')
                ]),
                'invalid' => __('invalid-m')
            ])),
            PhraseKey::PasswordsUser => Str::of(__('subject-invalid', [
                'subject' => __('email-address', [
                    'address' => __('address'),
                    'email' => __('email')
                ]),
                'invalid' => __('invalid-m')
            ])),

            PhraseKey::MINUTES => Str::of(__('minutes')),
            PhraseKey::ForgotPasswordExpireLine => Str::of(__('forgot-password-expire-line', [
                'password' => __('password')
            ])),
            PhraseKey::ForgotPasswordOtherwiseLine => Str::of(__('forgot-password-otherwise-line', [
                'password' => __('password')
            ])),
            PhraseKey::ForgotPasswordTitle => Str::of(__('reset-password', [
                'password' => __('password')
            ])),
            PhraseKey::ForgotPasswordText => Str::of(__('forgot-password-text', [
                'email' => __('email'),
                'reset-password' => __('reset-password', [
                    'password' => __('password')
                ])
            ])),
            PhraseKey::ForgotPasswordAction => Str::of(__('forgot-password-action', [
                'password' => __('password')
            ])),
            PhraseKey::LinkNotAllowed => Str::of(__('link-not-allowed', [
                'allowed' => __('allowed-m')
            ])),
            PhraseKey::NameAlreadyUsed => Str::of(__('value-already-used', [
                'subject' => __('name'),
                'used' => __('used-m')
            ])),
            PhraseKey::InvalidRoleRemotion => Str::of(__('invalid-subject-remotion', [
                'invalid' => __('invalid-f'),
                'subject' => __('role')
            ])),
            PhraseKey::InvalidRoleInclusion => Str::of(__('invalid-subject-inclusion', [
                'invalid' => __('invalid-f'),
                'subject' => __('role')
            ])),
            PhraseKey::InvalidAbilityRemotion => Str::of(__('invalid-subject-remotion', [
                'invalid' => __('invalid-f'),
                'subject' => __('ability')
            ])),
            PhraseKey::InvalidAbilityInclusion => Str::of(__('invalid-subject-inclusion', [
                'invalid' => __('invalid-f'),
                'subject' => __('ability')
            ])),
            PhraseKey::ValidMimes => Str::of(__('valid-mimes')),
            PhraseKey::MaxFileSizeInvalid => Str::of(__('max-file-size-invalid', [
                'maximum' => __('maximum'),
                'size' => __('size'),
            ]))
        };
        if ($uppercase) {
            return $message->append($remain)->ucfirst();
        }
        return $message->append($remain);
    }
}

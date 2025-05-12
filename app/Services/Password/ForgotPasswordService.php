<?php

declare(strict_types=1);

namespace App\Services\Password;

use App\Library\Builders\Phrase;
use App\Library\Enums\PhraseKey;
use App\Services\Password\Constracts\ForgotPasswordServiceInterfacer;
use Illuminate\Support\Facades\Password;
use Illuminate\Contracts\Auth\PasswordBroker;

class ForgotPasswordService implements ForgotPasswordServiceInterfacer
{
    public function sendResetLink(array $inputs): array
    {
        $status = Password::sendResetLink($inputs);
        switch ($status) {
            case PasswordBroker::PASSWORD_RESET:
                return [
                    'ok' => FALSE,
                    'message' => Phrase::pickSentence(PhraseKey::PasswordsReset)
                ];
            case Password::INVALID_USER:
                return [
                    'ok' => FALSE,
                    'message' => Phrase::pickSentence(PhraseKey::PasswordsUser)
                ];
            case Password::INVALID_TOKEN:
                return [
                    'ok' => FALSE,
                    'message' => Phrase::pickSentence(PhraseKey::PasswordsToken)
                ];
            case Password::RESET_THROTTLED:
                return [
                    'ok' => FALSE,
                    'message' => Phrase::pickSentence(PhraseKey::PasswordsThrottled)
                ];
            default:
                return [
                    'ok' => TRUE,
                    'message' => Phrase::pickSentence(PhraseKey::PasswordsSent)
                ];
        }
    }
}

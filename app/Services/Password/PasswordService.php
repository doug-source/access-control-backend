<?php

declare(strict_types=1);

namespace App\Services\Password;

use App\Library\Builders\Phrase;
use App\Library\Enums\PhraseKey;
use App\Services\Password\Constracts\PasswordServiceInterfacer;
use Illuminate\Support\Facades\Password;
use Illuminate\Contracts\Auth\PasswordBroker;
use \Closure;

class PasswordService implements PasswordServiceInterfacer
{
    /**
     * Define the output according to status
     *
     * @see Illuminate\Support\Facades\Password
     *
     * @return array{ok:bool, message:string}
     */
    private function defineOutput(string $status, string $successKey)
    {
        return match ($status) {
            Password::INVALID_USER => [
                'ok' => FALSE,
                'message' => Phrase::pickSentence(PhraseKey::PasswordsUser)
            ],
            Password::INVALID_TOKEN => [
                'ok' => FALSE,
                'message' => Phrase::pickSentence(PhraseKey::PasswordsToken)
            ],
            Password::RESET_THROTTLED => [
                'ok' => FALSE,
                'message' => Phrase::pickSentence(PhraseKey::PasswordsThrottled)
            ],
            PasswordBroker::PASSWORD_RESET => [
                'ok' => $successKey === PasswordBroker::PASSWORD_RESET,
                'message' => Phrase::pickSentence(PhraseKey::PasswordsReset)
            ],
            Password::RESET_LINK_SENT => [
                'ok' => $successKey === PasswordBroker::RESET_LINK_SENT,
                'message' => Phrase::pickSentence(PhraseKey::PasswordsSent)
            ]
        };
    }

    public function sendResetLink(array $inputs): array
    {
        $status = Password::sendResetLink($inputs);
        return $this->defineOutput($status, Password::RESET_LINK_SENT);
    }

    public function reset(array $credentials, Closure $callback): array
    {
        $status = Password::reset(
            $credentials,
            $callback
        );
        return $this->defineOutput($status, PasswordBroker::PASSWORD_RESET);
    }
}

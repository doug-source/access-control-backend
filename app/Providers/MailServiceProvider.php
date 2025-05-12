<?php

declare(strict_types=1);

namespace App\Providers;

use App\Library\Builders\EmailVerifyMail as EmailVerifyMailBuilder;
use App\Library\Builders\Phrase;
use App\Library\Builders\UrlExternal;
use App\Library\Enums\PhraseKey;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\HtmlString;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\ServiceProvider;
use Illuminate\Auth\Notifications\VerifyEmail;

final class MailServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $lastLine = Phrase::pickSentence(PhraseKey::Regards);
        $app = config('app.name');

        VerifyEmail::toMailUsing(function (object $notifiable, string $url) use ($lastLine, $app) {
            $greeting = Phrase::pickSentence(PhraseKey::Hello, ", {$notifiable->name}");
            $url = EmailVerifyMailBuilder::buildEmailButtonUrl(
                $url,
                config('app.frontend.uri.email-verify.form')
            );
            return (new MailMessage)
                ->greeting($greeting)
                ->subject(Phrase::pickSentence(PhraseKey::ConfirmationEmail))
                ->line(Phrase::pickSentence(PhraseKey::ConfirmationEmailText))
                ->action(Phrase::pickSentence(PhraseKey::ClickHere), $url)
                ->salutation(new HtmlString("$lastLine, <br><br>{$app}"));
        });

        ResetPassword::toMailUsing(function (object $notifiable, string $token) use ($lastLine, $app) {
            $greeting = Phrase::pickSentence(PhraseKey::Hello, ", {$notifiable->name}");
            $min = config('auth.passwords.users.expire');
            $minutes = Phrase::pickSentence(key: PhraseKey::MINUTES, uppercase: FALSE)->toString();
            $expireLine = Phrase::pickSentence(key: PhraseKey::ForgotPasswordExpireLine, remain: " {$min} {$minutes}.");
            $otherwiseLine = Phrase::pickSentence(PhraseKey::ForgotPasswordOtherwiseLine);

            return (new MailMessage)
                ->greeting($greeting)
                ->subject(Phrase::pickSentence(PhraseKey::ForgotPasswordTitle))
                ->line(Phrase::pickSentence(PhraseKey::ForgotPasswordTitle))
                ->action(
                    Phrase::pickSentence(PhraseKey::ForgotPasswordAction),
                    UrlExternal::build(
                        path: config('app.frontend.uri.change-password.form'),
                        query: [
                            'token' => $token
                        ]
                    )->value()
                )
                ->line(new HtmlString("$expireLine<br><br>$otherwiseLine"))
                ->salutation(new HtmlString("$lastLine, <br><br>{$app}"));
        });
    }
}

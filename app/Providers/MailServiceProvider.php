<?php

declare(strict_types=1);

namespace App\Providers;

use App\Library\Builders\EmailVerifyMail as EmailVerifyMailBuilder;
use App\Library\Builders\Phrase;
use App\Library\Enums\PhraseKey;
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
    }
}

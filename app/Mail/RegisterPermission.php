<?php

namespace App\Mail;

use App\Library\Builders\Phrase;
use App\Library\Enums\PhraseKey;
use Illuminate\Bus\Queueable;
// use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Address;

class RegisterPermission extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(public readonly array $data)
    {
        // ...
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address($this->data['fromEmail'], $this->data['fromName']),
            subject: $this->data['subject']
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.register.permission',
            with: [
                'url' => $this->data['url'],
                'heading' => Phrase::pickSentence(PhraseKey::Congratulations),
                'btnText' => Phrase::pickSentence(PhraseKey::ClickHere),
                'paragraph_1' => Phrase::pickSentence(PhraseKey::PreRegisterUserTextOne),
                'paragraph_2' => Phrase::pickSentence(PhraseKey::PreRegisterUserTextTwo),
                'regards' => Phrase::pickSentence(PhraseKey::Regards),
            ]
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}

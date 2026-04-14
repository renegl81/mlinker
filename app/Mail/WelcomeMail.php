<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class WelcomeMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly User $user,
        public readonly string $menuUrl,
        public readonly string $qrDownloadUrl,
    ) {}

    public function envelope(): Envelope
    {
        $locale = $this->user->locale ?? 'es';

        return new Envelope(
            subject: __('mail.welcome.subject', [], $locale),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.welcome',
        );
    }
}

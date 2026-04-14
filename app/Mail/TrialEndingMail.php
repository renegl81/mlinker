<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\Plan;
use App\Models\Tenant;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TrialEndingMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly Tenant $tenant,
        public readonly Plan $plan,
        public readonly \DateTimeInterface $trialEndsAt,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Tu periodo de prueba termina pronto - MenuLinker',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.trial-ending',
        );
    }
}

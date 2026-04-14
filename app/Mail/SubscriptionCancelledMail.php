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

class SubscriptionCancelledMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly Tenant $tenant,
        public readonly Plan $plan,
        public readonly ?\DateTimeInterface $endsAt,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Tu suscripcion ha sido cancelada - MenuLinker',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.subscription-cancelled',
        );
    }
}

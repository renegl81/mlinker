<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\Menu;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class MenuPublishedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly User $user,
        public readonly Menu $menu,
        public readonly string $publicUrl,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Tu menú '{$this->menu->name}' está publicado",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.menu-published',
        );
    }
}

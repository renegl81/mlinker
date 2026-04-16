<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\URL;

class AccountActivationNotification extends Notification
{
    public function __construct(public User $user) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $activationUrl = URL::temporarySignedRoute(
            'auth.activate',
            now()->addHours(24),
            ['user' => $this->user->id]
        );

        $locale = $this->user->locale ?? config('app.locale', 'es');

        return (new MailMessage)
            ->subject(__('auth.register.activation.subject', [], $locale))
            ->greeting(__('auth.register.activation.greeting', ['name' => $this->user->name], $locale))
            ->line(__('auth.register.activation.line1', [], $locale))
            ->action(__('auth.register.activation.action', [], $locale), $activationUrl)
            ->line(__('auth.register.activation.line2', [], $locale))
            ->line(__('auth.register.activation.line3', [], $locale));
    }
}

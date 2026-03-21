<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

final class OnboardingIncompleteNotification extends Notification
{
    use Queueable;

    public function __construct(
        private readonly User $user,
    ) {}

    /**
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    /**
     * @return array{title: string, message: string, type: string, action_url: string}
     */
    public function toDatabase(object $notifiable): array
    {
        return [
            'title' => 'Complete your profile',
            'message' => "You're almost there! Complete your profile to get started.",
            'type' => 'info',
            'action_url' => route('dashboard'),
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Complete your profile')
            ->greeting('Welcome, '.($this->user->name ?? 'there').'!')
            ->line("You're almost there. Complete your profile to unlock all features.")
            ->action('Get Started', route('dashboard'));
    }
}

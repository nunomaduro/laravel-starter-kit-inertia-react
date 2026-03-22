<?php

declare(strict_types=1);

namespace Modules\Billing\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Modules\Billing\Models\Subscription;

final class TrialEndingNotification extends Notification
{
    use Queueable;

    public function __construct(
        private readonly Subscription $subscription,
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
            'title' => 'Trial ending soon',
            'message' => sprintf(
                'Your trial ends in 3 days on %s.',
                $this->subscription->trial_ends_at?->toFormattedDateString() ?? 'soon',
            ),
            'type' => 'warning',
            'action_url' => route('billing.index'),
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Your trial is ending soon')
            ->greeting('Trial ending in 3 days!')
            ->line(sprintf(
                'Your trial period ends on %s. Upgrade now to keep access to all features.',
                $this->subscription->trial_ends_at?->toFormattedDateString() ?? 'soon',
            ))
            ->action('Upgrade Now', route('billing.index'));
    }
}

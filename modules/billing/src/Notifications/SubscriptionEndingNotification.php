<?php

declare(strict_types=1);

namespace Modules\Billing\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Modules\Billing\Models\Subscription;

final class SubscriptionEndingNotification extends Notification
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
            'title' => 'Subscription ending soon',
            'message' => sprintf(
                'Your subscription ends on %s.',
                $this->subscription->ends_at?->toFormattedDateString() ?? 'soon',
            ),
            'type' => 'warning',
            'action_url' => route('billing.index'),
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Your subscription is ending soon')
            ->greeting('Subscription ending soon')
            ->line(sprintf(
                'Your subscription will end on %s. Renew now to avoid any interruption.',
                $this->subscription->ends_at?->toFormattedDateString() ?? 'soon',
            ))
            ->action('Renew Subscription', route('billing.index'));
    }
}

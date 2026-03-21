<?php

declare(strict_types=1);

namespace App\Notifications\Billing;

use App\Models\Billing\FailedPaymentAttempt;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

final class DunningReminderNotification extends Notification
{
    use Queueable;

    public function __construct(
        private readonly FailedPaymentAttempt $attempt,
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
            'title' => 'Payment failed',
            'message' => sprintf(
                'Payment attempt #%d failed on %s. Please update your payment method to avoid service interruption.',
                $this->attempt->attempt_number,
                $this->attempt->failed_at?->toFormattedDateString() ?? 'recently',
            ),
            'type' => 'error',
            'action_url' => route('billing.index'),
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Action required: Payment failed')
            ->greeting('Payment failed')
            ->line(sprintf(
                'Attempt #%d to charge your payment method failed on %s.',
                $this->attempt->attempt_number,
                $this->attempt->failed_at?->toFormattedDateString() ?? 'recently',
            ))
            ->line('Please update your payment method to avoid service interruption.')
            ->action('Update Payment Method', route('billing.index'));
    }
}

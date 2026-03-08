<?php

declare(strict_types=1);

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

final class InvoicePaidNotification extends Notification
{
    use Queueable;

    public function __construct(
        private readonly string $invoiceId,
        private readonly string $amount,
    ) {}

    /**
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * @return array{title: string, message: string, type: string, action_url: string}
     */
    public function toDatabase(object $notifiable): array
    {
        return [
            'title' => 'Invoice paid',
            'message' => sprintf('Invoice %s for %s has been paid.', $this->invoiceId, $this->amount),
            'type' => 'success',
            'action_url' => route('billing.invoices.index'),
        ];
    }
}

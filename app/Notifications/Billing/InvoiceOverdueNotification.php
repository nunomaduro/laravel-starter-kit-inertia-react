<?php

declare(strict_types=1);

namespace App\Notifications\Billing;

use App\Models\Billing\Invoice;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

final class InvoiceOverdueNotification extends Notification
{
    use Queueable;

    public function __construct(
        private readonly Invoice $invoice,
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
            'title' => 'Invoice overdue',
            'message' => sprintf(
                'Invoice #%s for %s %s is overdue.',
                $this->invoice->number,
                number_format($this->invoice->total / 100, 2),
                mb_strtoupper($this->invoice->currency),
            ),
            'type' => 'error',
            'action_url' => route('billing.invoices.index'),
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $formattedTotal = number_format($this->invoice->total / 100, 2).' '.mb_strtoupper($this->invoice->currency);

        return (new MailMessage)
            ->subject('Invoice #'.$this->invoice->number.' is overdue')
            ->greeting('Invoice overdue')
            ->line('Invoice #'.$this->invoice->number.' for '.$formattedTotal.' is now overdue.')
            ->action('View Invoices', route('billing.invoices.index'));
    }
}

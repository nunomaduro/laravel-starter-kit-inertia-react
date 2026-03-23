<?php

declare(strict_types=1);

namespace Modules\Billing\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Modules\Billing\Actions\BuildLaravelDailyInvoice;
use Modules\Billing\Models\Invoice;

final class InvoicePaidNotification extends Notification
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
            'title' => 'Invoice paid',
            'message' => sprintf(
                'Invoice %s for %s %s has been paid.',
                $this->invoice->number,
                number_format($this->invoice->total / 100, 2),
                mb_strtoupper($this->invoice->currency),
            ),
            'type' => 'success',
            'action_url' => route('billing.invoices.index'),
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $laravelInvoice = (new BuildLaravelDailyInvoice)->handle($this->invoice);

        $formattedTotal = number_format($this->invoice->total / 100, 2).' '.mb_strtoupper($this->invoice->currency);

        return (new MailMessage)
            ->subject('Invoice '.$this->invoice->number.' – Payment Confirmed')
            ->greeting('Payment received!')
            ->line('Thank you. Invoice '.$this->invoice->number.' for '.$formattedTotal.' has been paid.')
            ->action('View Invoices', route('billing.invoices.index'))
            ->attachData(
                (string) $laravelInvoice->toHtml()->render(),
                'invoice-'.$this->invoice->number.'.html',
                ['mime' => 'text/html'],
            );
    }
}

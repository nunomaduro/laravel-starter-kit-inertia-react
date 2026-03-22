<?php

declare(strict_types=1);

namespace Modules\Billing\Observers;

use Modules\Billing\Models\Invoice;
use Modules\Billing\Notifications\InvoiceOverdueNotification;
use Modules\Billing\Notifications\InvoicePaidNotification;
use Thomasjohnkane\Snooze\ScheduledNotification;

final class InvoiceObserver
{
    public function created(Invoice $invoice): void
    {
        if ($invoice->due_date === null || $invoice->paid_at !== null) {
            return;
        }

        $invoice->loadMissing('organization.owner');
        $owner = $invoice->organization?->owner;

        if ($owner === null) {
            return;
        }

        $sendAt = $invoice->due_date->toDateTimeImmutable()->modify('+7 days');

        if ($sendAt <= now()) {
            return;
        }

        ScheduledNotification::create(
            $owner,
            new InvoiceOverdueNotification($invoice),
            $sendAt,
            ['invoice_id' => $invoice->id, 'notification_type' => InvoiceOverdueNotification::class],
        );
    }

    public function updated(Invoice $invoice): void
    {
        if (! $invoice->wasChanged('paid_at') || $invoice->paid_at === null) {
            return;
        }

        $invoice->loadMissing('organization.owner');
        $owner = $invoice->organization?->owner;

        if ($owner === null) {
            return;
        }

        // Notify owner immediately via database + mail
        $owner->notify(new InvoicePaidNotification($invoice));

        // Cancel any pending overdue notification for this invoice
        ScheduledNotification::findByMeta('invoice_id', $invoice->id)
            ->each(fn (ScheduledNotification $sn) => $sn->isSent() || $sn->isCancelled() ? null : $sn->cancel());
    }
}

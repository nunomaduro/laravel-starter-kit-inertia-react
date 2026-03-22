<?php

declare(strict_types=1);

namespace Modules\Billing\Observers;

use Modules\Billing\Models\FailedPaymentAttempt;
use Modules\Billing\Notifications\DunningReminderNotification;
use Thomasjohnkane\Snooze\ScheduledNotification;

final class FailedPaymentAttemptObserver
{
    public function created(FailedPaymentAttempt $attempt): void
    {
        $attempt->loadMissing('organization.owner');
        $owner = $attempt->organization?->owner;

        if ($owner === null) {
            return;
        }

        // Send immediately since there is no scheduled next_retry_at on the model.
        // Use a short delay (1 minute in the future) to ensure Snooze accepts the time.
        $sendAt = now()->addMinute();

        ScheduledNotification::create(
            $owner,
            new DunningReminderNotification($attempt),
            $sendAt,
            ['failed_payment_attempt_id' => $attempt->id, 'notification_type' => DunningReminderNotification::class],
        );
    }
}

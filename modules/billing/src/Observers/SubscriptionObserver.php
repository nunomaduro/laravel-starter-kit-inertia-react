<?php

declare(strict_types=1);

namespace Modules\Billing\Observers;

use App\Models\Organization;
use App\Models\User;
use Modules\Billing\Models\Subscription;
use Modules\Billing\Notifications\SubscriptionEndingNotification;
use Modules\Billing\Notifications\TrialEndingNotification;
use Thomasjohnkane\Snooze\ScheduledNotification;

final class SubscriptionObserver
{
    public function created(Subscription $subscription): void
    {
        if ($subscription->trial_ends_at === null || $subscription->trial_ends_at->isPast()) {
            return;
        }

        $owner = $this->resolveOwner($subscription);

        if ($owner === null) {
            return;
        }

        $sendAt = $subscription->trial_ends_at->subDays(3);

        if ($sendAt->isPast()) {
            return;
        }

        ScheduledNotification::create(
            $owner,
            new TrialEndingNotification($subscription),
            $sendAt,
            ['subscription_id' => $subscription->id, 'notification_type' => TrialEndingNotification::class],
        );
    }

    public function updated(Subscription $subscription): void
    {
        if (! $subscription->wasChanged('ends_at') || $subscription->ends_at === null) {
            return;
        }

        // Only schedule if ends_at is more than 7 days away (so the reminder would be in the future)
        if ($subscription->ends_at->isPast() || $subscription->ends_at->diffInDays(now()) <= 7) {
            return;
        }

        $owner = $this->resolveOwner($subscription);

        if ($owner === null) {
            return;
        }

        $sendAt = $subscription->ends_at->subDays(7);

        if ($sendAt->isPast()) {
            return;
        }

        ScheduledNotification::create(
            $owner,
            new SubscriptionEndingNotification($subscription),
            $sendAt,
            ['subscription_id' => $subscription->id, 'notification_type' => SubscriptionEndingNotification::class],
        );
    }

    private function resolveOwner(Subscription $subscription): ?User
    {
        $subscriber = $subscription->subscriber;

        if (! $subscriber instanceof Organization) {
            return null;
        }

        return $subscriber->owner;
    }
}

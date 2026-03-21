<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\User\UserCreated;
use App\Notifications\OnboardingIncompleteNotification;
use Thomasjohnkane\Snooze\ScheduledNotification;

final class ScheduleOnboardingReminderOnUserCreated
{
    public function handle(UserCreated $event): void
    {
        $sendAt = now()->addDays(2);

        ScheduledNotification::create(
            $event->user,
            new OnboardingIncompleteNotification($event->user),
            $sendAt,
            ['user_id' => $event->user->id, 'notification_type' => OnboardingIncompleteNotification::class],
        );
    }
}

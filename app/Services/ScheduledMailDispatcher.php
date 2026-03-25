<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\MailTriggerSchedule;
use Laravel\Pennant\Feature;

final class ScheduledMailDispatcher
{
    /**
     * Find the active schedule for a given event and organization.
     * Returns null if no active schedule exists or if the feature flag is inactive.
     */
    public function getScheduleForEvent(string $eventClass, int $organizationId): ?MailTriggerSchedule
    {
        $schedule = MailTriggerSchedule::query()
            ->where('event_class', $eventClass)
            ->where('organization_id', $organizationId)
            ->where('is_active', true)
            ->first();

        if (! $schedule instanceof MailTriggerSchedule) {
            return null;
        }

        if ($schedule->feature_flag !== null && ! Feature::active($schedule->feature_flag)) {
            return null;
        }

        return $schedule;
    }

    /**
     * Determine if a schedule has a delay configured.
     */
    public function shouldDelay(MailTriggerSchedule $schedule): bool
    {
        return $schedule->delay_minutes !== null && $schedule->delay_minutes > 0;
    }

    /**
     * Determine if an event should be suppressed for an organization.
     * An event is suppressed when a schedule record exists but is inactive.
     */
    public function shouldSuppress(string $eventClass, int $organizationId): bool
    {
        return MailTriggerSchedule::query()
            ->where('event_class', $eventClass)
            ->where('organization_id', $organizationId)
            ->where('is_active', false)
            ->exists();
    }
}

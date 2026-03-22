<?php

declare(strict_types=1);

namespace Modules\Billing\Jobs;

use App\Models\Organization;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Modules\Billing\Events\TrialEndingReminder;
use Modules\Billing\Models\Plan;
use Modules\Billing\Models\Subscription;
use Spatie\RateLimitedMiddleware\RateLimited;
use Throwable;

final class ProcessTrialEndingReminders implements ShouldQueue
{
    use Queueable;

    public function middleware(): array
    {
        return [
            (new RateLimited)
                ->allow(20)
                ->everySeconds(60)
                ->releaseAfterSeconds(60),
        ];
    }

    public function handle(): void
    {
        $reminderDays = [7, 3, 1];

        foreach ($reminderDays as $days) {
            $targetDate = now()->addDays($days)->toDateString();

            Subscription::query()
                ->whereDate('trial_ends_at', $targetDate)
                ->whereNull('canceled_at')
                ->where('subscriber_type', Organization::class)
                ->with(['plan', 'subscriber'])
                ->each(fn (Subscription $subscription) => $this->sendReminder($subscription, $days));
        }
    }

    private function sendReminder(Subscription $subscription, int $daysRemaining): void
    {
        try {
            $organization = $subscription->subscriber;

            if (! $organization instanceof Organization) {
                return;
            }

            $owner = $organization->owner;

            if (! $owner) {
                return;
            }

            $plan = $subscription->plan;
            $planName = $plan instanceof Plan && is_array($plan->name ?? null)
                ? (string) Arr::get($plan->name, 'en', Arr::first($plan->name) ?? 'your plan')
                : 'your plan';

            event(new TrialEndingReminder(
                organization: $organization,
                planName: $planName,
                daysRemaining: $daysRemaining,
                trialEndsAt: $subscription->trial_ends_at,
            ));

            Log::info('Trial ending reminder sent', [
                'organization_id' => $organization->id,
                'days_remaining' => $daysRemaining,
            ]);
        } catch (Throwable $throwable) {
            Log::error('Failed to send trial ending reminder', [
                'subscription_id' => $subscription->id,
                'error' => $throwable->getMessage(),
            ]);
        }
    }
}

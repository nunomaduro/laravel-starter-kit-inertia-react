<?php

declare(strict_types=1);

namespace App\Jobs\Billing;

use App\Events\Billing\DunningFailedPaymentReminder;
use App\Models\Billing\FailedPaymentAttempt;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Spatie\RateLimitedMiddleware\RateLimited;
use Throwable;

final class ProcessDunningReminders implements ShouldQueue
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
        $intervals = config('billing.dunning_intervals', [3, 7, 14]);

        FailedPaymentAttempt::query()
            ->with('organization')
            ->get()
            ->each(function (FailedPaymentAttempt $attempt) use ($intervals): void {
                $daysSinceFailure = (int) $attempt->failed_at->diffInDays(now(), false);
                $sentCount = $attempt->dunning_emails_sent ?? 0;

                if ($sentCount >= count($intervals)) {
                    return;
                }

                $nextInterval = $intervals[$sentCount];
                if ($daysSinceFailure < $nextInterval) {
                    return;
                }

                $owner = $attempt->organization->owner;
                if ($owner === null) {
                    return;
                }

                try {
                    event(new DunningFailedPaymentReminder(
                        $attempt->organization,
                        $attempt->attempt_number,
                        $nextInterval
                    ));
                    $attempt->update([
                        'last_dunning_sent_at' => now(),
                        'dunning_emails_sent' => $sentCount + 1,
                    ]);
                } catch (Throwable $throwable) {
                    Log::error('Dunning notification failed', [
                        'attempt_id' => $attempt->id,
                        'error' => $throwable->getMessage(),
                    ]);
                }
            });
    }
}

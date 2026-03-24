<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Events\NewTermsVersionPublished;
use App\Models\TermsVersion;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Spatie\RateLimitedMiddleware\RateLimited;
use Throwable;

final class NotifyUsersOfNewTermsVersion implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $timeout = 30;

    /** @var array<int, int> */
    public array $backoff = [10, 30];

    public function __construct(
        public readonly TermsVersion $termsVersion
    ) {}

    public function middleware(): array
    {
        return [
            (new RateLimited)
                ->allow(30)
                ->everySeconds(60)
                ->releaseAfterSeconds(90),
        ];
    }

    public function handle(): void
    {
        User::query()
            ->whereNotNull('email_verified_at')
            ->each(fn (User $user) => event(new NewTermsVersionPublished($this->termsVersion, $user)));
    }

    public function failed(Throwable $exception): void
    {
        Log::error('NotifyUsersOfNewTermsVersion failed', [
            'terms_version_id' => $this->termsVersion->getKey(),
            'error' => $exception->getMessage(),
        ]);
    }
}

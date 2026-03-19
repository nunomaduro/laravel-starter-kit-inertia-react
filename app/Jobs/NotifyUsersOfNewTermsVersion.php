<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Events\NewTermsVersionPublished;
use App\Models\TermsVersion;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Spatie\RateLimitedMiddleware\RateLimited;

final class NotifyUsersOfNewTermsVersion implements ShouldQueue
{
    use Queueable;

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
}

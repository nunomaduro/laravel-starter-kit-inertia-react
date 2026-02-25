<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Events\NewTermsVersionPublished;
use App\Models\TermsVersion;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

final class NotifyUsersOfNewTermsVersion implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly TermsVersion $termsVersion
    ) {}

    public function handle(): void
    {
        User::query()
            ->whereNotNull('email_verified_at')
            ->each(function (User $user): void {
                event(new NewTermsVersionPublished($this->termsVersion, $user));
            });
    }
}

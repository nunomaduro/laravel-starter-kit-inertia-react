<?php

declare(strict_types=1);

use App\Jobs\NotifyUsersOfNewTermsVersion;
use App\Models\TermsVersion;
use Illuminate\Support\Facades\Queue;
use Spatie\RateLimitedMiddleware\RateLimited;

it('has correct retry configuration', function (): void {
    $termsVersion = TermsVersion::factory()->create();
    $job = new NotifyUsersOfNewTermsVersion($termsVersion);

    expect($job->tries)->toBe(3)
        ->and($job->timeout)->toBe(30)
        ->and($job->backoff)->toBe([10, 30]);
});

it('has rate limited middleware', function (): void {
    $termsVersion = TermsVersion::factory()->create();
    $job = new NotifyUsersOfNewTermsVersion($termsVersion);

    $middleware = $job->middleware();

    expect($middleware)->toHaveCount(1)
        ->and($middleware[0])->toBeInstanceOf(RateLimited::class);
});

it('can be dispatched to the queue', function (): void {
    Queue::fake();

    $termsVersion = TermsVersion::factory()->create();

    NotifyUsersOfNewTermsVersion::dispatch($termsVersion);

    Queue::assertPushed(NotifyUsersOfNewTermsVersion::class);
});

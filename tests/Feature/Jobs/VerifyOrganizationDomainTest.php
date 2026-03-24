<?php

declare(strict_types=1);

use App\Jobs\VerifyOrganizationDomain;
use App\Models\OrganizationDomain;
use Illuminate\Support\Facades\Queue;
use Spatie\RateLimitedMiddleware\RateLimited;

it('has correct retry configuration', function (): void {
    $domain = OrganizationDomain::factory()->create();
    $job = new VerifyOrganizationDomain($domain);

    expect($job->tries)->toBe(3)
        ->and($job->timeout)->toBe(30)
        ->and($job->backoff)->toBe([10, 30, 60]);
});

it('has rate limited middleware', function (): void {
    $domain = OrganizationDomain::factory()->create();
    $job = new VerifyOrganizationDomain($domain);

    $middleware = $job->middleware();

    expect($middleware)->toHaveCount(1)
        ->and($middleware[0])->toBeInstanceOf(RateLimited::class);
});

it('can be dispatched to the queue', function (): void {
    Queue::fake();

    $domain = OrganizationDomain::factory()->create();

    VerifyOrganizationDomain::dispatch($domain);

    Queue::assertPushed(VerifyOrganizationDomain::class);
});

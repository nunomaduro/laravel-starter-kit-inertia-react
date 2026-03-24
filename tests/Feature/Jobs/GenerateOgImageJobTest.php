<?php

declare(strict_types=1);

use App\Jobs\GenerateOgImageJob;
use Illuminate\Support\Facades\Queue;

it('has correct retry configuration', function (): void {
    $user = createTestUser();
    $job = new GenerateOgImageJob($user, 'Test Title');

    expect($job->tries)->toBe(2)
        ->and($job->timeout)->toBe(60)
        ->and($job->backoff)->toBe([5, 15]);
});

it('can be dispatched to the queue', function (): void {
    Queue::fake();

    $user = createTestUser();

    GenerateOgImageJob::dispatch($user, 'Test Title');

    Queue::assertPushed(GenerateOgImageJob::class);
});

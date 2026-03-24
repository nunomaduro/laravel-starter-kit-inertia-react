<?php

declare(strict_types=1);

use App\Jobs\GenerateEmbedding;
use Illuminate\Support\Facades\Queue;

it('has correct retry configuration', function (): void {
    $user = createTestUser();
    $job = new GenerateEmbedding($user, 'name');

    expect($job->tries)->toBe(3)
        ->and($job->backoff)->toBe(60)
        ->and($job->timeout)->toBe(120);
});

it('can be dispatched to the queue', function (): void {
    Queue::fake();

    $user = createTestUser();

    GenerateEmbedding::dispatch($user, 'name');

    Queue::assertPushed(GenerateEmbedding::class);
});

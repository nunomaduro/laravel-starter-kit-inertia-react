<?php

declare(strict_types=1);

use App\Jobs\ProcessWebhookJob;
use Spatie\RateLimitedMiddleware\RateLimited;
use Spatie\WebhookClient\Models\WebhookCall;

it('has correct timeout configuration', function (): void {
    $webhookCall = WebhookCall::create([
        'name' => 'test',
        'url' => 'https://example.com/webhook',
        'payload' => ['key' => 'value'],
    ]);

    $job = new ProcessWebhookJob($webhookCall);

    expect($job->timeout)->toBe(60);
});

it('has rate limited middleware', function (): void {
    $webhookCall = WebhookCall::create([
        'name' => 'test',
        'url' => 'https://example.com/webhook',
        'payload' => ['key' => 'value'],
    ]);

    $job = new ProcessWebhookJob($webhookCall);
    $middleware = $job->middleware();

    expect($middleware)->toHaveCount(1)
        ->and($middleware[0])->toBeInstanceOf(RateLimited::class);
});

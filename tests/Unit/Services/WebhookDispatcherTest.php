<?php

declare(strict_types=1);

use App\Models\Organization;
use App\Models\WebhookEndpoint;
use App\Services\WebhookDispatcher;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Spatie\WebhookServer\CallWebhookJob;

beforeEach(function (): void {
    Queue::fake();
    Http::fake();
});

it('dispatches to active endpoints subscribed to the event', function (): void {
    $org = Organization::factory()->create();
    $endpoint = WebhookEndpoint::factory()
        ->for($org)
        ->forEvents(['order.created'])
        ->create();

    app(WebhookDispatcher::class)->dispatch('order.created', ['id' => 1], $org->id);

    Queue::assertPushed(CallWebhookJob::class, function (CallWebhookJob $job) use ($endpoint): bool {
        return $job->webhookUrl === $endpoint->url
            && $job->payload['event'] === 'order.created';
    });
});

it('skips inactive endpoints', function (): void {
    $org = Organization::factory()->create();
    WebhookEndpoint::factory()
        ->for($org)
        ->forEvents(['order.created'])
        ->inactive()
        ->create();

    app(WebhookDispatcher::class)->dispatch('order.created', ['id' => 1], $org->id);

    Queue::assertNotPushed(CallWebhookJob::class);
});

it('skips endpoints not subscribed to the event', function (): void {
    $org = Organization::factory()->create();
    WebhookEndpoint::factory()
        ->for($org)
        ->forEvents(['invoice.paid'])
        ->create();

    app(WebhookDispatcher::class)->dispatch('order.created', ['id' => 1], $org->id);

    Queue::assertNotPushed(CallWebhookJob::class);
});

it('skips endpoints from other organizations', function (): void {
    $org1 = Organization::factory()->create();
    $org2 = Organization::factory()->create();

    WebhookEndpoint::factory()
        ->for($org2)
        ->forEvents(['order.created'])
        ->create();

    app(WebhookDispatcher::class)->dispatch('order.created', ['id' => 1], $org1->id);

    Queue::assertNotPushed(CallWebhookJob::class);
});

it('testPing returns status and time_ms on success', function (): void {
    Http::fake([
        '*' => Http::response('OK', 200),
    ]);

    $org = Organization::factory()->create();
    $endpoint = WebhookEndpoint::factory()->for($org)->create();

    $result = app(WebhookDispatcher::class)->testPing($endpoint);

    expect($result)->toHaveKey('status', 200)
        ->toHaveKey('time_ms')
        ->and($result['time_ms'])->toBeGreaterThanOrEqual(0);
});

it('testPing returns error on connection failure', function (): void {
    Http::fake([
        '*' => fn () => throw new Illuminate\Http\Client\ConnectionException('Connection refused'),
    ]);

    $org = Organization::factory()->create();
    $endpoint = WebhookEndpoint::factory()->for($org)->create();

    $result = app(WebhookDispatcher::class)->testPing($endpoint);

    expect($result)->toHaveKey('error')
        ->toHaveKey('time_ms')
        ->not->toHaveKey('status');
});

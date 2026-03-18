<?php

declare(strict_types=1);

use App\Jobs\ProcessWebhookJob;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Queue;
use Spatie\WebhookServer\CallWebhookJob;
use Spatie\WebhookServer\WebhookCall;

beforeEach(function (): void {
    config(['webhook-client.configs' => [
        [
            'name' => 'default',
            'signing_secret' => 'test-secret',
            'signature_header_name' => 'Signature',
            'signature_validator' => Spatie\WebhookClient\SignatureValidator\DefaultSignatureValidator::class,
            'webhook_profile' => Spatie\WebhookClient\WebhookProfile\ProcessEverythingWebhookProfile::class,
            'webhook_response' => Spatie\WebhookClient\WebhookResponse\DefaultRespondsTo::class,
            'webhook_model' => Spatie\WebhookClient\Models\WebhookCall::class,
            'process_webhook_job' => ProcessWebhookJob::class,
        ],
    ]]);
});

test('webhook client accepts valid signed request and dispatches job', function (): void {
    Bus::fake([ProcessWebhookJob::class]);

    $payload = ['event' => 'test', 'data' => ['id' => 1]];
    $payloadJson = json_encode($payload);
    $signature = hash_hmac('sha256', $payloadJson, 'test-secret');

    $response = $this->postJson('webhooks/spatie', $payload, [
        'Signature' => $signature,
        'Content-Type' => 'application/json',
    ]);

    $response->assertStatus(200);
    Bus::assertDispatched(ProcessWebhookJob::class);
});

test('webhook client rejects invalid signature', function (): void {
    Bus::fake([ProcessWebhookJob::class]);

    $payload = ['event' => 'test'];
    $response = $this->postJson('webhooks/spatie', $payload, [
        'Signature' => 'invalid-signature',
        'Content-Type' => 'application/json',
    ]);

    $response->assertStatus(500);
    Bus::assertNotDispatched(ProcessWebhookJob::class);
});

test('webhook server dispatches job when sending webhook', function (): void {
    Queue::fake();

    WebhookCall::create()
        ->url('https://example.com/webhooks')
        ->payload(['event' => 'order.created', 'id' => 123])
        ->useSecret('my-secret')
        ->dispatch();

    Queue::assertPushed(CallWebhookJob::class);
});

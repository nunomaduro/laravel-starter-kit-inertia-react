<?php

declare(strict_types=1);

use App\Models\Billing\FailedPaymentAttempt;
use App\Models\Billing\Invoice;
use App\Models\Billing\Plan;
use App\Models\Billing\Subscription;
use App\Models\Billing\WebhookLog;
use App\Models\Organization;
use App\Models\User;
use App\Services\PaymentGateway\Contracts\PaymentGatewayInterface;
use App\Services\PaymentGateway\Gateways\PaddleGateway;
use Illuminate\Support\Facades\Cache;

beforeEach(function (): void {
    $this->webhookSecret = 'pdl_test_secret_for_testing';
    config(['paddle.webhook_secret' => $this->webhookSecret]);

    $this->owner = User::factory()->withoutTwoFactor()->create();
    $this->org = Organization::factory()->forOwner($this->owner)->create([
        'paddle_customer_id' => 'ctm_test_'.uniqid(),
    ]);
    $this->org->addMember($this->owner, 'admin');

    $this->plan = Plan::query()->create([
        'name' => ['en' => 'Test Plan'],
        'slug' => 'test-plan-'.uniqid(),
        'price' => 2900,
        'is_per_seat' => false,
        'price_per_seat' => 0,
        'currency' => 'usd',
        'invoice_period' => 1,
        'invoice_interval' => 'month',
    ]);
});

function paddlePayload(string $eventType, array $data): string
{
    return json_encode([
        'event_type' => $eventType,
        'data' => $data,
    ]);
}

function paddleSignature(string $payload, string $secret): string
{
    $timestamp = (string) time();
    $signed = $timestamp.':'.$payload;
    $hash = hash_hmac('sha256', $signed, $secret);

    return "ts={$timestamp};h1={$hash}";
}

function postPaddleWebhook(object $test, string $payload, string $signature): Illuminate\Testing\TestResponse
{
    return $test->call(
        'POST',
        '/webhooks/paddle',
        [],
        [],
        [],
        [
            'HTTP_PADDLE_SIGNATURE' => $signature,
            'CONTENT_TYPE' => 'application/json',
        ],
        $payload,
    );
}

function mockPaddleGatewayForEvent(string $eventType, array $data): void
{
    Cache::forget('billing.default_gateway_model');

    $mock = Mockery::mock(PaymentGatewayInterface::class);
    $mock->shouldReceive('validateWebhook')->andReturn(true);
    $mock->shouldReceive('handleWebhook')->andReturn([
        'event' => $eventType,
        'data' => $data,
    ]);

    app()->instance(PaddleGateway::class, $mock);
}

function mockPaddleGatewayInvalid(): void
{
    Cache::forget('billing.default_gateway_model');

    $mock = Mockery::mock(PaymentGatewayInterface::class);
    $mock->shouldReceive('validateWebhook')->andReturn(false);

    app()->instance(PaddleGateway::class, $mock);
}

// --- Signature Validation ---

it('rejects webhooks with an invalid signature', function (): void {
    mockPaddleGatewayInvalid();

    $payload = paddlePayload('subscription.created', [
        'id' => 'sub_test',
        'customer_id' => $this->org->paddle_customer_id,
    ]);

    $response = postPaddleWebhook($this, $payload, 'invalid_signature');

    $response->assertStatus(400);
    expect(WebhookLog::query()->count())->toBe(1);
    expect(WebhookLog::query()->first())
        ->gateway->toBe('paddle')
        ->processed->toBeFalse();
});

it('rejects webhooks with an empty signature header', function (): void {
    mockPaddleGatewayInvalid();

    $payload = paddlePayload('transaction.completed', [
        'id' => 'txn_test',
        'customer_id' => $this->org->paddle_customer_id,
    ]);

    $response = postPaddleWebhook($this, $payload, '');

    $response->assertStatus(400);
});

// --- subscription.created ---

it('handles subscription.created webhook', function (): void {
    $subscriptionId = 'sub_paddle_'.uniqid();

    $subscription = $this->org->planSubscriptions()->create([
        'name' => ['en' => 'Test'],
        'slug' => 'test-'.uniqid(),
        'plan_id' => $this->plan->id,
        'quantity' => 1,
        'starts_at' => now(),
        'ends_at' => now()->addMonth(),
    ]);

    $data = [
        'id' => $subscriptionId,
        'customer_id' => $this->org->paddle_customer_id,
        'status' => 'active',
        'items' => [
            ['quantity' => 1, 'price' => ['id' => 'pri_test']],
        ],
    ];

    mockPaddleGatewayForEvent('subscription.created', $data);

    $payload = paddlePayload('subscription.created', $data);

    $response = postPaddleWebhook($this, $payload, 'mocked');

    $response->assertStatus(200);

    $subscription->refresh();
    expect($subscription->gateway_subscription_id)->toBe($subscriptionId);

    $log = WebhookLog::query()->latest('id')->first();
    expect($log)
        ->gateway->toBe('paddle')
        ->event_type->toBe('subscription.created')
        ->processed->toBeTrue()
        ->organization_id->toBe($this->org->id);
});

// --- subscription.updated ---

it('handles subscription.updated webhook', function (): void {
    $subscriptionId = 'sub_paddle_'.uniqid();

    $subscription = $this->org->planSubscriptions()->create([
        'name' => ['en' => 'Test'],
        'slug' => 'test-'.uniqid(),
        'plan_id' => $this->plan->id,
        'gateway_subscription_id' => $subscriptionId,
        'quantity' => 1,
        'starts_at' => now(),
        'ends_at' => now()->addMonth(),
    ]);

    $data = [
        'id' => $subscriptionId,
        'customer_id' => $this->org->paddle_customer_id,
        'status' => 'active',
        'items' => [
            ['quantity' => 5, 'price' => ['id' => 'pri_test']],
        ],
    ];

    mockPaddleGatewayForEvent('subscription.updated', $data);

    $payload = paddlePayload('subscription.updated', $data);

    $response = postPaddleWebhook($this, $payload, 'mocked');

    $response->assertStatus(200);

    $subscription->refresh();
    expect($subscription->quantity)->toBe(5)
        ->and($subscription->canceled_at)->toBeNull();
});

it('handles subscription.updated with canceled status', function (): void {
    $subscriptionId = 'sub_paddle_'.uniqid();

    $subscription = $this->org->planSubscriptions()->create([
        'name' => ['en' => 'Test'],
        'slug' => 'test-'.uniqid(),
        'plan_id' => $this->plan->id,
        'gateway_subscription_id' => $subscriptionId,
        'quantity' => 1,
        'starts_at' => now(),
        'ends_at' => now()->addMonth(),
    ]);

    $data = [
        'id' => $subscriptionId,
        'customer_id' => $this->org->paddle_customer_id,
        'status' => 'canceled',
        'items' => [
            ['quantity' => 1, 'price' => ['id' => 'pri_test']],
        ],
    ];

    mockPaddleGatewayForEvent('subscription.updated', $data);

    $payload = paddlePayload('subscription.updated', $data);

    $response = postPaddleWebhook($this, $payload, 'mocked');

    $response->assertStatus(200);

    $subscription->refresh();
    expect($subscription->canceled_at)->not->toBeNull();
});

// --- subscription.canceled ---

it('handles subscription.canceled webhook', function (): void {
    $subscriptionId = 'sub_paddle_'.uniqid();

    $subscription = $this->org->planSubscriptions()->create([
        'name' => ['en' => 'Test'],
        'slug' => 'test-'.uniqid(),
        'plan_id' => $this->plan->id,
        'gateway_subscription_id' => $subscriptionId,
        'quantity' => 1,
        'starts_at' => now(),
        'ends_at' => now()->addMonth(),
    ]);

    $effectiveDate = now()->addDays(30)->toIso8601String();

    $data = [
        'id' => $subscriptionId,
        'customer_id' => $this->org->paddle_customer_id,
        'status' => 'canceled',
        'scheduled_change' => [
            'effective_at' => $effectiveDate,
        ],
    ];

    mockPaddleGatewayForEvent('subscription.canceled', $data);

    $payload = paddlePayload('subscription.canceled', $data);

    $response = postPaddleWebhook($this, $payload, 'mocked');

    $response->assertStatus(200);

    $subscription->refresh();
    expect($subscription->canceled_at)->not->toBeNull()
        ->and($subscription->ends_at)->not->toBeNull();

    $log = WebhookLog::query()->latest('id')->first();
    expect($log)
        ->event_type->toBe('subscription.canceled')
        ->processed->toBeTrue()
        ->organization_id->toBe($this->org->id);
});

// --- transaction.completed ---

it('handles transaction.completed webhook and creates invoice', function (): void {
    $transactionId = 'txn_paddle_'.uniqid();

    $data = [
        'id' => $transactionId,
        'customer_id' => $this->org->paddle_customer_id,
        'currency_code' => 'usd',
        'details' => [
            'totals' => [
                'total' => '29.00',
            ],
        ],
    ];

    mockPaddleGatewayForEvent('transaction.completed', $data);

    $payload = paddlePayload('transaction.completed', $data);

    $response = postPaddleWebhook($this, $payload, 'mocked');

    $response->assertStatus(200);

    $invoice = Invoice::query()->withoutGlobalScopes()->where('gateway_invoice_id', $transactionId)->first();
    expect($invoice)->not->toBeNull()
        ->and($invoice->organization_id)->toBe($this->org->id)
        ->and($invoice->status)->toBe('paid')
        ->and($invoice->total)->toBe(2900)
        ->and($invoice->subtotal)->toBe(2900)
        ->and($invoice->tax)->toBe(0)
        ->and($invoice->currency)->toBe('USD')
        ->and($invoice->number)->toBe('PDL-'.mb_substr($transactionId, 0, 20))
        ->and($invoice->paid_at)->not->toBeNull();

    $log = WebhookLog::query()->latest('id')->first();
    expect($log)
        ->event_type->toBe('transaction.completed')
        ->processed->toBeTrue()
        ->organization_id->toBe($this->org->id);
});

it('handles transaction.completed webhook with existing invoice (idempotent)', function (): void {
    $transactionId = 'txn_paddle_'.uniqid();

    Invoice::query()->withoutGlobalScopes()->create([
        'organization_id' => $this->org->id,
        'billable_type' => Organization::class,
        'billable_id' => $this->org->id,
        'gateway_invoice_id' => $transactionId,
        'number' => 'PDL-OLD',
        'status' => 'open',
        'subtotal' => 1000,
        'tax' => 0,
        'total' => 1000,
        'currency' => 'usd',
    ]);

    $data = [
        'id' => $transactionId,
        'customer_id' => $this->org->paddle_customer_id,
        'currency_code' => 'usd',
        'details' => [
            'totals' => [
                'total' => '29.00',
            ],
        ],
    ];

    mockPaddleGatewayForEvent('transaction.completed', $data);

    $payload = paddlePayload('transaction.completed', $data);

    $response = postPaddleWebhook($this, $payload, 'mocked');

    $response->assertStatus(200);

    $invoices = Invoice::query()->withoutGlobalScopes()->where('gateway_invoice_id', $transactionId)->get();
    expect($invoices)->toHaveCount(1)
        ->and($invoices->first()->status)->toBe('paid')
        ->and($invoices->first()->total)->toBe(2900);
});

// --- transaction.payment_failed ---

it('handles transaction.payment_failed webhook', function (): void {
    $subscriptionId = 'sub_paddle_'.uniqid();

    $data = [
        'customer_id' => $this->org->paddle_customer_id,
        'subscription_id' => $subscriptionId,
    ];

    mockPaddleGatewayForEvent('transaction.payment_failed', $data);

    $payload = paddlePayload('transaction.payment_failed', $data);

    $response = postPaddleWebhook($this, $payload, 'mocked');

    $response->assertStatus(200);

    $attempt = FailedPaymentAttempt::query()
        ->where('organization_id', $this->org->id)
        ->where('gateway', 'paddle')
        ->first();

    expect($attempt)->not->toBeNull()
        ->and($attempt->attempt_number)->toBe(1)
        ->and($attempt->gateway_subscription_id)->toBe($subscriptionId)
        ->and($attempt->failed_at)->not->toBeNull();

    $log = WebhookLog::query()->latest('id')->first();
    expect($log)
        ->event_type->toBe('transaction.payment_failed')
        ->processed->toBeTrue()
        ->organization_id->toBe($this->org->id);
});

it('increments attempt number on repeated payment failures', function (): void {
    $subscriptionId = 'sub_paddle_'.uniqid();

    FailedPaymentAttempt::query()->create([
        'organization_id' => $this->org->id,
        'gateway' => 'paddle',
        'gateway_subscription_id' => $subscriptionId,
        'attempt_number' => 2,
        'failed_at' => now()->subDay(),
    ]);

    $data = [
        'customer_id' => $this->org->paddle_customer_id,
        'subscription_id' => $subscriptionId,
    ];

    mockPaddleGatewayForEvent('transaction.payment_failed', $data);

    $payload = paddlePayload('transaction.payment_failed', $data);

    $response = postPaddleWebhook($this, $payload, 'mocked');

    $response->assertStatus(200);

    $attempt = FailedPaymentAttempt::query()
        ->where('organization_id', $this->org->id)
        ->where('gateway', 'paddle')
        ->where('gateway_subscription_id', $subscriptionId)
        ->first();

    expect($attempt->attempt_number)->toBe(3);
});

// --- WebhookLog ---

it('logs all webhook requests to WebhookLog table', function (): void {
    $data = [
        'id' => 'sub_test',
        'customer_id' => $this->org->paddle_customer_id,
        'status' => 'active',
    ];

    mockPaddleGatewayForEvent('subscription.created', $data);

    $payload = paddlePayload('subscription.created', $data);

    $response = postPaddleWebhook($this, $payload, 'mocked');

    $response->assertStatus(200);

    expect(WebhookLog::query()->count())->toBe(1);

    $log = WebhookLog::query()->first();
    expect($log)
        ->gateway->toBe('paddle')
        ->processed->toBeTrue()
        ->and($log->payload)->toBeArray()
        ->and($log->payload)->toHaveKey('event_type');
});

// --- Unknown Organization ---

it('handles webhook for unknown customer gracefully', function (): void {
    $data = [
        'id' => 'sub_unknown',
        'customer_id' => 'ctm_nonexistent',
        'status' => 'active',
    ];

    mockPaddleGatewayForEvent('subscription.created', $data);

    $payload = paddlePayload('subscription.created', $data);

    $response = postPaddleWebhook($this, $payload, 'mocked');

    $response->assertStatus(200);

    $log = WebhookLog::query()->first();
    expect($log)
        ->event_type->toBe('subscription.created')
        ->processed->toBeFalse()
        ->organization_id->toBeNull();
});

// --- Unknown Event Type ---

it('handles unknown event types gracefully', function (): void {
    $data = [
        'id' => 'adj_test',
        'customer_id' => $this->org->paddle_customer_id,
    ];

    mockPaddleGatewayForEvent('adjustment.created', $data);

    $payload = paddlePayload('adjustment.created', $data);

    $response = postPaddleWebhook($this, $payload, 'mocked');

    $response->assertStatus(200);

    $log = WebhookLog::query()->first();
    expect($log)
        ->event_type->toBe('adjustment.created')
        ->processed->toBeFalse();
});

// --- customer.created (no-op) ---

it('handles customer.created webhook as no-op', function (): void {
    $data = [
        'id' => 'ctm_new_'.uniqid(),
        'email' => 'test@example.com',
    ];

    mockPaddleGatewayForEvent('customer.created', $data);

    $payload = paddlePayload('customer.created', $data);

    $response = postPaddleWebhook($this, $payload, 'mocked');

    $response->assertStatus(200);

    $log = WebhookLog::query()->first();
    expect($log)
        ->event_type->toBe('customer.created')
        ->processed->toBeFalse()
        ->organization_id->toBeNull();
});

<?php

declare(strict_types=1);

use App\Events\Billing\InvoicePaid;
use App\Models\Billing\Invoice;
use App\Models\Billing\Plan;
use App\Models\Billing\Subscription;
use App\Models\Billing\WebhookLog;
use App\Models\Organization;
use App\Models\User;
use App\Services\PaymentGateway\Contracts\PaymentGatewayInterface;
use App\Services\PaymentGateway\Gateways\StripeGateway;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Event;

beforeEach(function (): void {
    $this->webhookSecret = 'whsec_test_secret_for_testing';
    config(['stripe.webhook_secret' => $this->webhookSecret]);

    $this->owner = User::factory()->withoutTwoFactor()->create();
    $this->org = Organization::factory()->forOwner($this->owner)->create([
        'stripe_customer_id' => 'cus_test_'.uniqid(),
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

function stripePayload(string $eventType, array $object, string $customerId): string
{
    return json_encode([
        'id' => 'evt_test_'.uniqid(),
        'type' => $eventType,
        'data' => [
            'object' => array_merge(['customer' => $customerId], $object),
        ],
    ]);
}

function stripeSignature(string $payload, string $secret): string
{
    $timestamp = time();
    $signedPayload = $timestamp.'.'.$payload;
    $signature = hash_hmac('sha256', $signedPayload, $secret);

    return "t={$timestamp},v1={$signature}";
}

function postStripeWebhook(object $test, string $payload, string $signature): Illuminate\Testing\TestResponse
{
    return $test->call(
        'POST',
        '/webhooks/stripe',
        [],
        [],
        [],
        [
            'HTTP_STRIPE_SIGNATURE' => $signature,
            'CONTENT_TYPE' => 'application/json',
        ],
        $payload,
    );
}

function mockGatewayForEvent(string $eventType, array $data): void
{
    Cache::forget('billing.default_gateway_model');

    $mock = Mockery::mock(PaymentGatewayInterface::class);
    $mock->shouldReceive('validateWebhook')->andReturn(true);
    $mock->shouldReceive('handleWebhook')->andReturn([
        'event' => $eventType,
        'data' => $data,
    ]);

    app()->instance(StripeGateway::class, $mock);
}

function mockGatewayInvalid(): void
{
    Cache::forget('billing.default_gateway_model');

    $mock = Mockery::mock(PaymentGatewayInterface::class);
    $mock->shouldReceive('validateWebhook')->andReturn(false);

    app()->instance(StripeGateway::class, $mock);
}

// --- Signature Validation ---

it('rejects webhooks with an invalid signature', function (): void {
    mockGatewayInvalid();

    $payload = stripePayload('customer.subscription.created', ['id' => 'sub_test'], $this->org->stripe_customer_id);

    $response = postStripeWebhook($this, $payload, 'invalid_signature');

    $response->assertStatus(400);
    expect(WebhookLog::query()->count())->toBe(1);
    expect(WebhookLog::query()->first())
        ->gateway->toBe('stripe')
        ->processed->toBeFalse();
});

it('rejects webhooks with an empty signature header', function (): void {
    mockGatewayInvalid();

    $payload = stripePayload('invoice.paid', ['id' => 'in_test'], $this->org->stripe_customer_id);

    $response = postStripeWebhook($this, $payload, '');

    $response->assertStatus(400);
});

// --- customer.subscription.created ---

it('handles customer.subscription.created webhook', function (): void {
    $subscriptionId = 'sub_stripe_'.uniqid();

    $subscription = $this->org->planSubscriptions()->create([
        'name' => ['en' => 'Test'],
        'slug' => 'test-'.uniqid(),
        'plan_id' => $this->plan->id,
        'quantity' => 1,
        'starts_at' => now(),
        'ends_at' => now()->addMonth(),
    ]);

    $data = [
        'object' => [
            'id' => $subscriptionId,
            'customer' => $this->org->stripe_customer_id,
            'status' => 'active',
        ],
    ];

    mockGatewayForEvent('customer.subscription.created', $data);

    $payload = json_encode(['type' => 'customer.subscription.created', 'data' => $data]);

    $response = postStripeWebhook($this, $payload, 'mocked');

    $response->assertStatus(200);

    $subscription->refresh();
    expect($subscription->gateway_subscription_id)->toBe($subscriptionId);

    $log = WebhookLog::query()->latest('id')->first();
    expect($log)
        ->gateway->toBe('stripe')
        ->event_type->toBe('customer.subscription.created')
        ->processed->toBeTrue()
        ->organization_id->toBe($this->org->id);
});

// --- customer.subscription.updated ---

it('handles customer.subscription.updated webhook', function (): void {
    $subscriptionId = 'sub_stripe_'.uniqid();

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
        'object' => [
            'id' => $subscriptionId,
            'customer' => $this->org->stripe_customer_id,
            'status' => 'active',
            'items' => [
                'data' => [['quantity' => 5]],
            ],
        ],
    ];

    mockGatewayForEvent('customer.subscription.updated', $data);

    $payload = json_encode(['type' => 'customer.subscription.updated', 'data' => $data]);

    $response = postStripeWebhook($this, $payload, 'mocked');

    $response->assertStatus(200);

    $subscription->refresh();
    expect($subscription->quantity)->toBe(5)
        ->and($subscription->canceled_at)->toBeNull();
});

it('handles customer.subscription.updated with canceled status', function (): void {
    $subscriptionId = 'sub_stripe_'.uniqid();

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
        'object' => [
            'id' => $subscriptionId,
            'customer' => $this->org->stripe_customer_id,
            'status' => 'canceled',
            'items' => [
                'data' => [['quantity' => 1]],
            ],
        ],
    ];

    mockGatewayForEvent('customer.subscription.updated', $data);

    $payload = json_encode(['type' => 'customer.subscription.updated', 'data' => $data]);

    $response = postStripeWebhook($this, $payload, 'mocked');

    $response->assertStatus(200);

    $subscription->refresh();
    expect($subscription->canceled_at)->not->toBeNull();
});

// --- customer.subscription.deleted ---

it('handles customer.subscription.deleted webhook', function (): void {
    $subscriptionId = 'sub_stripe_'.uniqid();

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
        'object' => [
            'id' => $subscriptionId,
            'customer' => $this->org->stripe_customer_id,
        ],
    ];

    mockGatewayForEvent('customer.subscription.deleted', $data);

    $payload = json_encode(['type' => 'customer.subscription.deleted', 'data' => $data]);

    $response = postStripeWebhook($this, $payload, 'mocked');

    $response->assertStatus(200);

    $subscription->refresh();
    expect($subscription->canceled_at)->not->toBeNull()
        ->and($subscription->ends_at)->not->toBeNull();

    $log = WebhookLog::query()->latest('id')->first();
    expect($log)
        ->event_type->toBe('customer.subscription.deleted')
        ->processed->toBeTrue()
        ->organization_id->toBe($this->org->id);
});

// --- invoice.paid ---

it('handles invoice.paid webhook and creates invoice', function (): void {
    Event::fake([InvoicePaid::class]);

    $invoiceId = 'in_stripe_'.uniqid();

    $data = [
        'object' => [
            'id' => $invoiceId,
            'customer' => $this->org->stripe_customer_id,
            'amount_paid' => 2900,
            'subtotal' => 2900,
            'tax' => 0,
            'currency' => 'usd',
            'number' => 'INV-2026-001',
        ],
    ];

    mockGatewayForEvent('invoice.paid', $data);

    $payload = json_encode(['type' => 'invoice.paid', 'data' => $data]);

    $response = postStripeWebhook($this, $payload, 'mocked');

    $response->assertStatus(200);

    $invoice = Invoice::query()->withoutGlobalScopes()->where('gateway_invoice_id', $invoiceId)->first();
    expect($invoice)->not->toBeNull()
        ->and($invoice->organization_id)->toBe($this->org->id)
        ->and($invoice->status)->toBe('paid')
        ->and($invoice->total)->toBe(2900)
        ->and($invoice->subtotal)->toBe(2900)
        ->and($invoice->tax)->toBe(0)
        ->and($invoice->currency)->toBe('USD')
        ->and($invoice->number)->toBe('INV-2026-001')
        ->and($invoice->paid_at)->not->toBeNull();

    Event::assertDispatched(InvoicePaid::class);

    $log = WebhookLog::query()->latest('id')->first();
    expect($log)
        ->event_type->toBe('invoice.paid')
        ->processed->toBeTrue()
        ->organization_id->toBe($this->org->id);
});

it('handles invoice.paid webhook with existing invoice (idempotent)', function (): void {
    Event::fake([InvoicePaid::class]);

    $invoiceId = 'in_stripe_'.uniqid();

    Invoice::query()->withoutGlobalScopes()->create([
        'organization_id' => $this->org->id,
        'billable_type' => Organization::class,
        'billable_id' => $this->org->id,
        'gateway_invoice_id' => $invoiceId,
        'number' => 'INV-OLD',
        'status' => 'open',
        'subtotal' => 1000,
        'tax' => 0,
        'total' => 1000,
        'currency' => 'usd',
    ]);

    $data = [
        'object' => [
            'id' => $invoiceId,
            'customer' => $this->org->stripe_customer_id,
            'amount_paid' => 2900,
            'subtotal' => 2900,
            'tax' => 0,
            'currency' => 'usd',
            'number' => 'INV-2026-002',
        ],
    ];

    mockGatewayForEvent('invoice.paid', $data);

    $payload = json_encode(['type' => 'invoice.paid', 'data' => $data]);

    $response = postStripeWebhook($this, $payload, 'mocked');

    $response->assertStatus(200);

    $invoices = Invoice::query()->withoutGlobalScopes()->where('gateway_invoice_id', $invoiceId)->get();
    expect($invoices)->toHaveCount(1)
        ->and($invoices->first()->status)->toBe('paid')
        ->and($invoices->first()->total)->toBe(2900);

    Event::assertDispatched(InvoicePaid::class);
});

// --- invoice.payment_failed ---

it('handles invoice.payment_failed webhook', function (): void {
    $invoiceId = 'in_stripe_'.uniqid();

    Invoice::query()->withoutGlobalScopes()->create([
        'organization_id' => $this->org->id,
        'billable_type' => Organization::class,
        'billable_id' => $this->org->id,
        'gateway_invoice_id' => $invoiceId,
        'number' => 'INV-FAIL-001',
        'status' => 'paid',
        'subtotal' => 2900,
        'tax' => 0,
        'total' => 2900,
        'currency' => 'usd',
    ]);

    $data = [
        'object' => [
            'id' => $invoiceId,
            'customer' => $this->org->stripe_customer_id,
        ],
    ];

    mockGatewayForEvent('invoice.payment_failed', $data);

    $payload = json_encode(['type' => 'invoice.payment_failed', 'data' => $data]);

    $response = postStripeWebhook($this, $payload, 'mocked');

    $response->assertStatus(200);

    $invoice = Invoice::query()->withoutGlobalScopes()->where('gateway_invoice_id', $invoiceId)->first();
    expect($invoice->status)->toBe('open');

    $log = WebhookLog::query()->latest('id')->first();
    expect($log)
        ->event_type->toBe('invoice.payment_failed')
        ->processed->toBeTrue()
        ->organization_id->toBe($this->org->id);
});

// --- WebhookLog ---

it('logs all webhook requests to WebhookLog table', function (): void {
    $data = [
        'object' => [
            'id' => 'sub_test',
            'customer' => $this->org->stripe_customer_id,
            'status' => 'active',
        ],
    ];

    mockGatewayForEvent('customer.subscription.created', $data);

    $payload = json_encode(['type' => 'customer.subscription.created', 'data' => $data]);

    $response = postStripeWebhook($this, $payload, 'mocked');

    $response->assertStatus(200);

    expect(WebhookLog::query()->count())->toBe(1);

    $log = WebhookLog::query()->first();
    expect($log)
        ->gateway->toBe('stripe')
        ->processed->toBeTrue()
        ->and($log->payload)->toBeArray()
        ->and($log->payload)->toHaveKey('type');
});

// --- Unknown Organization ---

it('handles webhook for unknown customer gracefully', function (): void {
    $data = [
        'object' => [
            'id' => 'sub_unknown',
            'customer' => 'cus_nonexistent',
            'status' => 'active',
        ],
    ];

    mockGatewayForEvent('customer.subscription.created', $data);

    $payload = json_encode(['type' => 'customer.subscription.created', 'data' => $data]);

    $response = postStripeWebhook($this, $payload, 'mocked');

    $response->assertStatus(200);

    $log = WebhookLog::query()->first();
    expect($log)
        ->event_type->toBe('customer.subscription.created')
        ->processed->toBeFalse()
        ->organization_id->toBeNull();
});

// --- Unknown Event Type ---

it('handles unknown event types gracefully', function (): void {
    $data = [
        'object' => [
            'id' => 'pi_test',
            'customer' => $this->org->stripe_customer_id,
        ],
    ];

    mockGatewayForEvent('payment_intent.succeeded', $data);

    $payload = json_encode(['type' => 'payment_intent.succeeded', 'data' => $data]);

    $response = postStripeWebhook($this, $payload, 'mocked');

    $response->assertStatus(200);

    $log = WebhookLog::query()->first();
    expect($log)
        ->event_type->toBe('payment_intent.succeeded')
        ->processed->toBeFalse();
});

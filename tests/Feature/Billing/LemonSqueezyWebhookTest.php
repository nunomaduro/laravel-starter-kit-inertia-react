<?php

declare(strict_types=1);

use App\Enums\Billing\CreditTransactionType;
use App\Listeners\Billing\AddCreditsFromLemonSqueezyOrder;
use App\Models\Billing\Credit;
use App\Models\Organization;
use App\Models\User;
use App\Services\TenantContext;
use Illuminate\Support\Facades\Event;
use LemonSqueezy\Laravel\Events\OrderCreated;
use LemonSqueezy\Laravel\Events\OrderRefunded;

beforeEach(function (): void {
    $this->signingSecret = 'ls_test_signing_secret_for_testing';
    config(['lemon-squeezy.signing_secret' => $this->signingSecret]);

    $this->owner = User::factory()->withoutTwoFactor()->create();
    $this->org = Organization::factory()->forOwner($this->owner)->create();
    $this->org->addMember($this->owner, 'admin');

    TenantContext::set($this->org);
});

function lemonSqueezyPayload(string $eventName, int $billableId, string $billableType, array $customData = [], array $attributeOverrides = []): array
{
    return [
        'meta' => [
            'event_name' => $eventName,
            'custom_data' => array_merge([
                'billable_id' => $billableId,
                'billable_type' => $billableType,
            ], $customData),
        ],
        'data' => [
            'id' => 'ls_order_'.uniqid(),
            'type' => 'orders',
            'attributes' => array_merge([
                'customer_id' => 12345,
                'first_order_item' => [
                    'product_id' => 1,
                    'variant_id' => 1,
                ],
                'identifier' => (string) Illuminate\Support\Str::uuid(),
                'order_number' => random_int(10000, 99999),
                'currency' => 'USD',
                'subtotal' => 5000,
                'discount_total' => 0,
                'tax' => 0,
                'total' => 5000,
                'tax_name' => null,
                'status' => 'paid',
                'urls' => ['receipt' => 'https://example.com/receipt'],
                'refunded' => false,
                'refunded_at' => null,
                'created_at' => now()->toIso8601String(),
            ], $attributeOverrides),
        ],
    ];
}

function lemonSqueezySignature(string $payload, string $secret): string
{
    return hash_hmac('sha256', $payload, $secret);
}

function postLemonSqueezyWebhook(object $test, string $payload, string $signature): Illuminate\Testing\TestResponse
{
    return $test->call(
        'POST',
        '/lemon-squeezy/webhook',
        [],
        [],
        [],
        [
            'HTTP_X_SIGNATURE' => $signature,
            'CONTENT_TYPE' => 'application/json',
        ],
        $payload,
    );
}

// --- Signature Validation ---

it('rejects webhooks with an invalid signature', function (): void {
    $payload = json_encode(lemonSqueezyPayload(
        'order_created',
        $this->org->id,
        Organization::class,
    ));

    $response = postLemonSqueezyWebhook($this, $payload, 'invalid_signature');

    $response->assertStatus(403);
});

it('rejects webhooks with an empty signature header', function (): void {
    $payload = json_encode(lemonSqueezyPayload(
        'order_created',
        $this->org->id,
        Organization::class,
    ));

    $response = postLemonSqueezyWebhook($this, $payload, '');

    $response->assertStatus(403);
});

it('accepts webhooks with a valid signature and dispatches WebhookReceived', function (): void {
    Event::fake();

    $payload = json_encode(lemonSqueezyPayload(
        'order_created',
        $this->org->id,
        Organization::class,
    ));

    $signature = lemonSqueezySignature($payload, $this->signingSecret);

    $response = postLemonSqueezyWebhook($this, $payload, $signature);

    // Signature was accepted (not 403) — vendor controller processes past signature check
    expect($response->getStatusCode())->not->toBe(403);
    Event::assertDispatched(LemonSqueezy\Laravel\Events\WebhookReceived::class);
});

// --- order_created → OrderCreated event → AddCreditsFromLemonSqueezyOrder listener ---

it('adds credits from explicit custom_data when OrderCreated is dispatched', function (): void {
    $payload = lemonSqueezyPayload(
        'order_created',
        $this->org->id,
        Organization::class,
        ['credits' => 100],
        ['total' => 5000],
    );

    $event = new OrderCreated($this->org, null, $payload);

    $listener = new AddCreditsFromLemonSqueezyOrder;
    $listener->handle($event);

    $credit = Credit::query()
        ->where('organization_id', $this->org->id)
        ->where('type', CreditTransactionType::Purchase)
        ->first();

    expect($credit)->not->toBeNull()
        ->and($credit->amount)->toBe(100)
        ->and($credit->running_balance)->toBe(100)
        ->and($credit->description)->toBe('Lemon Squeezy one-time purchase')
        ->and($credit->metadata)->toHaveKey('lemon_squeezy_order_id')
        ->and($credit->metadata)->toHaveKey('order_number');
});

it('calculates credits from total amount when no explicit credits in metadata', function (): void {
    config(['billing.lemon_squeezy_cents_per_credit' => 10]);

    $payload = lemonSqueezyPayload(
        'order_created',
        $this->org->id,
        Organization::class,
        [],
        ['total' => 5000],
    );

    $event = new OrderCreated($this->org, null, $payload);

    $listener = new AddCreditsFromLemonSqueezyOrder;
    $listener->handle($event);

    $credit = Credit::query()
        ->where('organization_id', $this->org->id)
        ->where('type', CreditTransactionType::Purchase)
        ->first();

    expect($credit)->not->toBeNull()
        ->and($credit->amount)->toBe(500);
});

it('includes credit_pack_id in metadata when provided', function (): void {
    $payload = lemonSqueezyPayload(
        'order_created',
        $this->org->id,
        Organization::class,
        ['credits' => 50, 'credit_pack_id' => 42],
    );

    $event = new OrderCreated($this->org, null, $payload);

    $listener = new AddCreditsFromLemonSqueezyOrder;
    $listener->handle($event);

    $credit = Credit::query()
        ->where('organization_id', $this->org->id)
        ->where('type', CreditTransactionType::Purchase)
        ->first();

    expect($credit)->not->toBeNull()
        ->and($credit->metadata)->toHaveKey('credit_pack_id')
        ->and($credit->metadata['credit_pack_id'])->toBe(42);
});

it('sets credit expiration based on billing config', function (): void {
    config(['billing.credit_expiration_days' => 365]);

    $payload = lemonSqueezyPayload(
        'order_created',
        $this->org->id,
        Organization::class,
        ['credits' => 100],
    );

    $event = new OrderCreated($this->org, null, $payload);

    $listener = new AddCreditsFromLemonSqueezyOrder;
    $listener->handle($event);

    $credit = Credit::query()
        ->where('organization_id', $this->org->id)
        ->where('type', CreditTransactionType::Purchase)
        ->first();

    expect($credit)->not->toBeNull()
        ->and($credit->expires_at)->not->toBeNull()
        ->and($credit->expires_at->isFuture())->toBeTrue()
        ->and($credit->expires_at->diffInDays(now(), absolute: true))->toBeGreaterThanOrEqual(364);
});

it('does not add credits when billable is not an Organization', function (): void {
    $user = User::factory()->withoutTwoFactor()->create();

    $payload = lemonSqueezyPayload(
        'order_created',
        $user->id,
        User::class,
        ['credits' => 100],
    );

    $event = new OrderCreated($user, null, $payload);

    $listener = new AddCreditsFromLemonSqueezyOrder;
    $listener->handle($event);

    expect(Credit::query()->count())->toBe(0);
});

it('does not add credits when total amount is zero', function (): void {
    $payload = lemonSqueezyPayload(
        'order_created',
        $this->org->id,
        Organization::class,
        [],
        ['total' => 0],
    );

    $event = new OrderCreated($this->org, null, $payload);

    $listener = new AddCreditsFromLemonSqueezyOrder;
    $listener->handle($event);

    expect(Credit::query()->count())->toBe(0);
});

it('accumulates credits across multiple orders', function (): void {
    $listener = new AddCreditsFromLemonSqueezyOrder;

    $payload1 = lemonSqueezyPayload(
        'order_created',
        $this->org->id,
        Organization::class,
        ['credits' => 50],
    );
    $listener->handle(new OrderCreated($this->org, null, $payload1));

    $payload2 = lemonSqueezyPayload(
        'order_created',
        $this->org->id,
        Organization::class,
        ['credits' => 30],
    );
    $listener->handle(new OrderCreated($this->org, null, $payload2));

    $credits = Credit::query()
        ->where('organization_id', $this->org->id)
        ->orderBy('id')
        ->get();

    expect($credits)->toHaveCount(2)
        ->and($credits[0]->amount)->toBe(50)
        ->and($credits[0]->running_balance)->toBe(50)
        ->and($credits[1]->amount)->toBe(30)
        ->and($credits[1]->running_balance)->toBe(80);
});

it('handles custom cents_per_credit configuration', function (): void {
    config(['billing.lemon_squeezy_cents_per_credit' => 25]);

    $payload = lemonSqueezyPayload(
        'order_created',
        $this->org->id,
        Organization::class,
        [],
        ['total' => 5000],
    );

    $event = new OrderCreated($this->org, null, $payload);

    $listener = new AddCreditsFromLemonSqueezyOrder;
    $listener->handle($event);

    $credit = Credit::query()
        ->where('organization_id', $this->org->id)
        ->first();

    expect($credit)->not->toBeNull()
        ->and($credit->amount)->toBe(200);
});

it('handles zero cents_per_credit gracefully', function (): void {
    config(['billing.lemon_squeezy_cents_per_credit' => 0]);

    $payload = lemonSqueezyPayload(
        'order_created',
        $this->org->id,
        Organization::class,
        [],
        ['total' => 5000],
    );

    $event = new OrderCreated($this->org, null, $payload);

    $listener = new AddCreditsFromLemonSqueezyOrder;
    $listener->handle($event);

    expect(Credit::query()->count())->toBe(0);
});

// --- order_refunded ---

it('does not reverse credits on OrderRefunded (no listener implemented)', function (): void {
    $this->org->addCredits(100, CreditTransactionType::Purchase, 'Initial purchase');

    event(new OrderRefunded($this->org, null, [
        'meta' => ['event_name' => 'order_refunded'],
        'data' => [
            'id' => 'ls_order_test',
            'attributes' => [
                'refunded' => true,
                'refunded_at' => now()->toIso8601String(),
            ],
        ],
    ]));

    expect($this->org->creditBalance())->toBe(100);
    expect(Credit::query()->where('organization_id', $this->org->id)->count())->toBe(1);
});

// --- Webhook dispatches events ---

it('dispatches WebhookReceived event on valid webhook for any event type', function (): void {
    Event::fake();

    $payload = json_encode([
        'meta' => [
            'event_name' => 'subscription_created',
            'custom_data' => [
                'billable_id' => $this->org->id,
                'billable_type' => Organization::class,
            ],
        ],
        'data' => [
            'id' => 'ls_sub_'.uniqid(),
            'type' => 'subscriptions',
            'attributes' => [
                'customer_id' => 12345,
                'status' => 'active',
                'product_id' => '1',
                'variant_id' => '1',
            ],
        ],
    ]);

    $signature = lemonSqueezySignature($payload, $this->signingSecret);

    postLemonSqueezyWebhook($this, $payload, $signature);

    Event::assertDispatched(LemonSqueezy\Laravel\Events\WebhookReceived::class);
});

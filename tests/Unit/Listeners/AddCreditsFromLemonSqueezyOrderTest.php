<?php

declare(strict_types=1);

use App\Enums\Billing\CreditTransactionType;
use App\Listeners\Billing\AddCreditsFromLemonSqueezyOrder;
use App\Models\Billing\Credit;
use App\Models\Organization;
use App\Models\User;
use App\Services\TenantContext;
use Illuminate\Support\Facades\Log;
use LemonSqueezy\Laravel\Events\OrderCreated;

beforeEach(function (): void {
    $this->owner = User::factory()->withoutTwoFactor()->create();
    $this->org = Organization::factory()->forOwner($this->owner)->create();
    $this->org->addMember($this->owner, 'admin');

    TenantContext::set($this->org);

    $this->listener = new AddCreditsFromLemonSqueezyOrder;
});

function orderPayload(int $billableId, array $customData = [], array $attributeOverrides = []): array
{
    return [
        'meta' => [
            'event_name' => 'order_created',
            'custom_data' => array_merge([
                'billable_id' => $billableId,
                'billable_type' => 'organization',
            ], $customData),
        ],
        'data' => [
            'id' => 'ls_order_'.uniqid(),
            'type' => 'orders',
            'attributes' => array_merge([
                'customer_id' => 12345,
                'order_number' => random_int(10000, 99999),
                'currency' => 'USD',
                'total' => 5000,
                'status' => 'paid',
                'created_at' => now()->toIso8601String(),
            ], $attributeOverrides),
        ],
    ];
}

it('adds credits from explicit custom_data credits', function (): void {
    $payload = orderPayload($this->org->id, ['credits' => 100]);
    $event = new OrderCreated($this->org, null, $payload);

    $this->listener->handle($event);

    $credit = Credit::query()->withoutGlobalScopes()->latest('id')->first();

    expect($credit)->not->toBeNull()
        ->and($credit->amount)->toBe(100)
        ->and($credit->running_balance)->toBe(100)
        ->and($credit->type)->toBe(CreditTransactionType::Purchase)
        ->and($credit->description)->toBe('Lemon Squeezy one-time purchase')
        ->and($credit->metadata)->toHaveKey('lemon_squeezy_order_id');
});

it('calculates credits from total amount when no explicit credits', function (): void {
    $payload = orderPayload($this->org->id, [], ['total' => 5000]);
    $event = new OrderCreated($this->org, null, $payload);

    $this->listener->handle($event);

    $credit = Credit::query()->withoutGlobalScopes()->latest('id')->first();

    // 5000 cents / 10 cents per credit = 500 credits
    expect($credit)->not->toBeNull()
        ->and($credit->amount)->toBe(500)
        ->and($credit->running_balance)->toBe(500);
});

it('uses custom cents_per_credit config for fallback calculation', function (): void {
    config(['billing.lemon_squeezy_cents_per_credit' => 50]);

    $payload = orderPayload($this->org->id, [], ['total' => 5000]);
    $event = new OrderCreated($this->org, null, $payload);

    $this->listener->handle($event);

    $credit = Credit::query()->withoutGlobalScopes()->latest('id')->first();

    // 5000 cents / 50 cents per credit = 100 credits
    expect($credit)->not->toBeNull()
        ->and($credit->amount)->toBe(100);
});

it('prefers explicit credits over amount calculation', function (): void {
    $payload = orderPayload($this->org->id, ['credits' => 42], ['total' => 5000]);
    $event = new OrderCreated($this->org, null, $payload);

    $this->listener->handle($event);

    $credit = Credit::query()->withoutGlobalScopes()->latest('id')->first();

    expect($credit)->not->toBeNull()
        ->and($credit->amount)->toBe(42);
});

it('returns early when billable is not an Organization', function (): void {
    $user = User::factory()->withoutTwoFactor()->create();
    $payload = orderPayload($user->id, ['credits' => 100]);
    $event = new OrderCreated($user, null, $payload);

    $this->listener->handle($event);

    expect(Credit::query()->withoutGlobalScopes()->count())->toBe(0);
});

it('returns early when total amount is zero', function (): void {
    $payload = orderPayload($this->org->id, [], ['total' => 0]);
    $event = new OrderCreated($this->org, null, $payload);

    $this->listener->handle($event);

    expect(Credit::query()->withoutGlobalScopes()->count())->toBe(0);
});

it('returns early when total amount is negative', function (): void {
    $payload = orderPayload($this->org->id, [], ['total' => -100]);
    $event = new OrderCreated($this->org, null, $payload);

    $this->listener->handle($event);

    expect(Credit::query()->withoutGlobalScopes()->count())->toBe(0);
});

it('returns zero credits when cents_per_credit is zero', function (): void {
    config(['billing.lemon_squeezy_cents_per_credit' => 0]);

    Log::shouldReceive('warning')
        ->once()
        ->withArgs(fn (string $message) => str_contains($message, 'no credits to add'));

    $payload = orderPayload($this->org->id, [], ['total' => 5000]);
    $event = new OrderCreated($this->org, null, $payload);

    $this->listener->handle($event);

    expect(Credit::query()->withoutGlobalScopes()->count())->toBe(0);
});

it('includes credit_pack_id in metadata when provided', function (): void {
    $payload = orderPayload($this->org->id, ['credits' => 50, 'credit_pack_id' => 7]);
    $event = new OrderCreated($this->org, null, $payload);

    $this->listener->handle($event);

    $credit = Credit::query()->withoutGlobalScopes()->latest('id')->first();

    expect($credit->metadata)
        ->toHaveKey('credit_pack_id', 7)
        ->toHaveKey('lemon_squeezy_order_id')
        ->toHaveKey('order_number');
});

it('omits credit_pack_id from metadata when not provided', function (): void {
    $payload = orderPayload($this->org->id, ['credits' => 50]);
    $event = new OrderCreated($this->org, null, $payload);

    $this->listener->handle($event);

    $credit = Credit::query()->withoutGlobalScopes()->latest('id')->first();

    expect($credit->metadata)
        ->not->toHaveKey('credit_pack_id')
        ->toHaveKey('lemon_squeezy_order_id');
});

it('sets expiration based on credit_expiration_days config', function (): void {
    config(['billing.credit_expiration_days' => 30]);

    $payload = orderPayload($this->org->id, ['credits' => 50]);
    $event = new OrderCreated($this->org, null, $payload);

    $this->listener->handle($event);

    $credit = Credit::query()->withoutGlobalScopes()->latest('id')->first();

    expect($credit->expires_at)->not->toBeNull()
        ->and($credit->expires_at->isFuture())->toBeTrue()
        ->and($credit->expires_at->diffInDays(now(), absolute: true))->toBeBetween(29, 31);
});

it('sets no expiration when credit_expiration_days is null', function (): void {
    config(['billing.credit_expiration_days' => null]);

    $payload = orderPayload($this->org->id, ['credits' => 50]);
    $event = new OrderCreated($this->org, null, $payload);

    $this->listener->handle($event);

    $credit = Credit::query()->withoutGlobalScopes()->latest('id')->first();

    expect($credit->expires_at)->toBeNull();
});

it('accumulates running balance across multiple orders', function (): void {
    $payload1 = orderPayload($this->org->id, ['credits' => 100]);
    $payload2 = orderPayload($this->org->id, ['credits' => 50]);

    $this->listener->handle(new OrderCreated($this->org, null, $payload1));
    $this->listener->handle(new OrderCreated($this->org, null, $payload2));

    $credits = Credit::query()->withoutGlobalScopes()->orderBy('id')->get();

    expect($credits)->toHaveCount(2)
        ->and($credits[0]->amount)->toBe(100)
        ->and($credits[0]->running_balance)->toBe(100)
        ->and($credits[1]->amount)->toBe(50)
        ->and($credits[1]->running_balance)->toBe(150);
});

it('handles missing total attribute gracefully', function (): void {
    $payload = orderPayload($this->org->id);
    unset($payload['data']['attributes']['total']);

    $event = new OrderCreated($this->org, null, $payload);

    $this->listener->handle($event);

    expect(Credit::query()->withoutGlobalScopes()->count())->toBe(0);
});

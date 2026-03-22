<?php

declare(strict_types=1);

use App\Models\Organization;
use App\Models\User;
use App\Settings\BillingSettings;
use Modules\Billing\Actions\SyncSubscriptionSeatsAction;
use Modules\Billing\Models\Plan;
use Modules\Billing\Models\Subscription;

beforeEach(function (): void {
    $this->plan = Plan::query()->create([
        'name' => ['en' => 'Per-seat plan'],
        'slug' => 'per-seat-'.uniqid(),
        'price' => 0,
        'is_per_seat' => true,
        'price_per_seat' => 10,
        'currency' => 'usd',
        'invoice_period' => 1,
        'invoice_interval' => 'month',
    ]);

    $this->org = Organization::factory()->create();
    $this->org->addMember(User::factory()->withoutTwoFactor()->create(), 'admin');
});

it('does nothing when seat-based billing is disabled', function (): void {
    resolve(BillingSettings::class)->enable_seat_based_billing = false;

    $subscription = $this->org->planSubscriptions()->create([
        'name' => ['en' => 'Test sub'],
        'slug' => 'test-'.uniqid(),
        'plan_id' => $this->plan->id,
        'quantity' => 1,
        'starts_at' => now(),
        'ends_at' => now()->addMonth(),
    ]);

    $action = resolve(SyncSubscriptionSeatsAction::class);
    $action->handle($this->org);

    expect($subscription->fresh()->quantity)->toBe(1);
});

it('updates subscription quantity when seat billing enabled and member count differs', function (): void {
    resolve(BillingSettings::class)->enable_seat_based_billing = true;

    $subscription = $this->org->planSubscriptions()->create([
        'name' => ['en' => 'Test sub'],
        'slug' => 'test-'.uniqid(),
        'plan_id' => $this->plan->id,
        'quantity' => 1,
        'starts_at' => now(),
        'ends_at' => now()->addMonth(),
    ]);

    $this->org->addMember(User::factory()->withoutTwoFactor()->create(), 'member');

    $action = resolve(SyncSubscriptionSeatsAction::class);
    $action->handle($this->org);

    expect($subscription->fresh()->quantity)->toBe(2);
});

it('identifies per-seat plans correctly', function (): void {
    $perSeat = Plan::query()->create([
        'name' => ['en' => 'Per seat'],
        'slug' => 'ps-'.uniqid(),
        'price' => 0,
        'is_per_seat' => true,
        'price_per_seat' => 5,
        'currency' => 'usd',
        'invoice_period' => 1,
        'invoice_interval' => 'month',
    ]);

    $flat = Plan::query()->create([
        'name' => ['en' => 'Flat'],
        'slug' => 'flat-'.uniqid(),
        'price' => 99,
        'is_per_seat' => false,
        'price_per_seat' => 0,
        'currency' => 'usd',
        'invoice_period' => 1,
        'invoice_interval' => 'month',
    ]);

    expect($perSeat->isPerSeat())->toBeTrue()
        ->and($flat->isPerSeat())->toBeFalse();
});

it('subscription seatCount returns quantity', function (): void {
    $sub = new Subscription(['quantity' => 5]);
    expect($sub->seatCount())->toBe(5);
});

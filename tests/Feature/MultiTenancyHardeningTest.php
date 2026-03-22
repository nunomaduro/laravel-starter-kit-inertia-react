<?php

declare(strict_types=1);

use App\Models\Organization;
use App\Models\User;
use App\Services\TenantContext;
use Modules\Billing\Models\Credit;
use Modules\Billing\Models\RefundRequest;
use Modules\Billing\Policies\CreditPolicy;
use Modules\Billing\Policies\RefundRequestPolicy;

it('denies viewing credit from another organization', function (): void {
    $user = User::factory()->withoutTwoFactor()->create();
    $orgA = Organization::factory()->create();
    $orgB = Organization::factory()->create();
    $orgA->addMember($user, 'admin');

    TenantContext::set($orgA);

    $credit = Credit::query()->create([
        'organization_id' => $orgB->id,
        'creditable_type' => Organization::class,
        'creditable_id' => $orgB->id,
        'amount' => 100,
        'running_balance' => 100,
        'type' => 'purchase',
    ]);

    $policy = resolve(CreditPolicy::class);
    expect($policy->view($user, $credit))->toBeFalse();
});

it('allows viewing credit from same organization', function (): void {
    $user = User::factory()->withoutTwoFactor()->create();
    $org = Organization::factory()->create();
    $org->addMember($user, 'admin');

    TenantContext::set($org);

    $credit = Credit::query()->create([
        'organization_id' => $org->id,
        'creditable_type' => Organization::class,
        'creditable_id' => $org->id,
        'amount' => 100,
        'running_balance' => 100,
        'type' => 'purchase',
    ]);

    $policy = resolve(CreditPolicy::class);
    expect($policy->view($user, $credit))->toBeTrue();
});

it('denies viewing refund request from another organization', function (): void {
    $user = User::factory()->withoutTwoFactor()->create();
    $orgA = Organization::factory()->create();
    $orgB = Organization::factory()->create();
    $orgA->addMember($user, 'admin');

    TenantContext::set($orgA);

    $invoice = $orgB->invoices()->create([
        'billable_type' => Organization::class,
        'billable_id' => $orgB->id,
        'number' => 'INV-'.uniqid(),
        'status' => 'paid',
        'currency' => 'usd',
        'subtotal' => 1000,
        'tax' => 0,
        'total' => 1000,
    ]);

    $refund = RefundRequest::query()->create([
        'organization_id' => $orgB->id,
        'invoice_id' => $invoice->id,
        'amount' => 500,
        'status' => 'pending',
    ]);

    $policy = resolve(RefundRequestPolicy::class);
    expect($policy->view($user, $refund))->toBeFalse();
});

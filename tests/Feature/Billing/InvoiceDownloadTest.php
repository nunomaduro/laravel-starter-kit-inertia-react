<?php

declare(strict_types=1);

use App\Models\Billing\Invoice;
use App\Models\Organization;
use App\Models\User;
use App\Services\TenantContext;
use App\Settings\SetupWizardSettings;

beforeEach(function (): void {
    $setup = resolve(SetupWizardSettings::class);
    $setup->setup_completed = true;

    $this->user = User::withoutEvents(fn (): User => User::factory()->withoutTwoFactor()->create());
    $this->organization = Organization::factory()->create();
    $this->organization->addMember($this->user, 'admin');
    $this->user->organizations()->updateExistingPivot($this->organization->id, ['is_default' => true]);
    TenantContext::set($this->organization);
});

it('returns pdf when downloading invoice', function (): void {
    $invoice = Invoice::withoutGlobalScopes()->create([
        'organization_id' => $this->organization->id,
        'billable_type' => Organization::class,
        'billable_id' => $this->organization->id,
        'number' => 'INV-TEST-001',
        'status' => 'paid',
        'currency' => 'USD',
        'subtotal' => 10000,
        'tax' => 0,
        'total' => 10000,
        'paid_at' => now(),
        'due_date' => now()->addDays(14),
        'line_items' => null,
        'billing_address' => null,
    ]);

    $response = $this->actingAs($this->user)
        ->withSession(['current_organization_id' => $this->organization->id])
        ->get(route('billing.invoices.download', $invoice));

    $response->assertOk();
    $response->assertHeader('Content-Type', 'application/pdf');
    $response->assertHeader('Content-Disposition', 'attachment; filename="invoice-INV-TEST-001.pdf"');
});

it('uses line items when present', function (): void {
    $invoice = Invoice::withoutGlobalScopes()->create([
        'organization_id' => $this->organization->id,
        'billable_type' => Organization::class,
        'billable_id' => $this->organization->id,
        'number' => 'INV-TEST-002',
        'status' => 'paid',
        'currency' => 'USD',
        'subtotal' => 5000,
        'tax' => 500,
        'total' => 5500,
        'paid_at' => now(),
        'due_date' => now()->addDays(14),
        'line_items' => [
            ['name' => 'Plan Pro', 'quantity' => 1, 'price' => 5000, 'total' => 5000],
        ],
        'billing_address' => ['name' => 'Acme Inc', 'address' => '123 Main St'],
    ]);

    $response = $this->actingAs($this->user)
        ->withSession(['current_organization_id' => $this->organization->id])
        ->get(route('billing.invoices.download', $invoice));

    $response->assertOk();
    $response->assertHeader('Content-Type', 'application/pdf');
});

it('denies download for invoice of another organization', function (): void {
    $otherOrg = Organization::factory()->create();
    $invoice = Invoice::withoutGlobalScopes()->create([
        'organization_id' => $otherOrg->id,
        'billable_type' => Organization::class,
        'billable_id' => $otherOrg->id,
        'number' => 'INV-OTHER-001',
        'status' => 'paid',
        'currency' => 'USD',
        'subtotal' => 1000,
        'tax' => 0,
        'total' => 1000,
        'paid_at' => now(),
        'due_date' => now()->addDays(14),
        'line_items' => null,
        'billing_address' => null,
    ]);

    $this->actingAs($this->user)
        ->withSession(['current_organization_id' => $this->organization->id])
        ->get(route('billing.invoices.download', $invoice))
        ->assertNotFound();
});

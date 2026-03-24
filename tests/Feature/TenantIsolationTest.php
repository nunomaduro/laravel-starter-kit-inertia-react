<?php

declare(strict_types=1);

use App\Models\AuditLog;
use App\Models\Organization;
use App\Models\OrganizationDomain;
use App\Models\SlugRedirect;
use App\Services\TenantContext;

/*
|--------------------------------------------------------------------------
| Task 27: Tenant Isolation Tests
|--------------------------------------------------------------------------
|
| Verify that BelongsToOrganization models are scoped correctly via
| TenantContext, ensuring cross-org data is inaccessible.
|
*/

// ── AuditLog Isolation ───────────────────────────────────────────────────

it('scopes AuditLog queries to the current organization', function (): void {
    $orgA = Organization::factory()->create();
    $orgB = Organization::factory()->create();

    AuditLog::query()->withoutGlobalScopes()->create([
        'organization_id' => $orgA->id,
        'actor_id' => null,
        'actor_type' => 'system',
        'action' => 'test.orgA',
        'created_at' => now(),
    ]);

    AuditLog::query()->withoutGlobalScopes()->create([
        'organization_id' => $orgB->id,
        'actor_id' => null,
        'actor_type' => 'system',
        'action' => 'test.orgB',
        'created_at' => now(),
    ]);

    TenantContext::set($orgA);

    $logs = AuditLog::query()->get();

    expect($logs)->toHaveCount(1)
        ->and($logs->first()->action)->toBe('test.orgA');
});

it('prevents access to AuditLog from another organization', function (): void {
    $orgA = Organization::factory()->create();
    $orgB = Organization::factory()->create();

    $log = AuditLog::query()->withoutGlobalScopes()->create([
        'organization_id' => $orgB->id,
        'actor_id' => null,
        'actor_type' => 'system',
        'action' => 'cross-org.test',
        'created_at' => now(),
    ]);

    TenantContext::set($orgA);

    expect(AuditLog::query()->find($log->id))->toBeNull();
});

it('auto-assigns organization_id to AuditLog on create when TenantContext is set', function (): void {
    $org = Organization::factory()->create();
    TenantContext::set($org);

    $log = AuditLog::query()->create([
        'actor_id' => null,
        'actor_type' => 'system',
        'action' => 'auto.assign.test',
        'created_at' => now(),
    ]);

    expect($log->organization_id)->toBe($org->id);
});

// ── OrganizationDomain Isolation ─────────────────────────────────────────

it('scopes OrganizationDomain queries to the current organization', function (): void {
    $orgA = Organization::factory()->create();
    $orgB = Organization::factory()->create();

    OrganizationDomain::query()->withoutGlobalScopes()->create([
        'organization_id' => $orgA->id,
        'domain' => 'a.example.com',
        'type' => 'custom',
        'status' => 'pending_dns',
    ]);

    OrganizationDomain::query()->withoutGlobalScopes()->create([
        'organization_id' => $orgB->id,
        'domain' => 'b.example.com',
        'type' => 'custom',
        'status' => 'pending_dns',
    ]);

    TenantContext::set($orgA);

    $domains = OrganizationDomain::query()->get();

    expect($domains)->toHaveCount(1)
        ->and($domains->first()->domain)->toBe('a.example.com');
});

it('prevents access to OrganizationDomain from another organization', function (): void {
    $orgA = Organization::factory()->create();
    $orgB = Organization::factory()->create();

    $domain = OrganizationDomain::query()->withoutGlobalScopes()->create([
        'organization_id' => $orgB->id,
        'domain' => 'cross.example.com',
        'type' => 'custom',
        'status' => 'active',
    ]);

    TenantContext::set($orgA);

    expect(OrganizationDomain::query()->find($domain->id))->toBeNull();
});

it('auto-assigns organization_id to OrganizationDomain on create when TenantContext is set', function (): void {
    $org = Organization::factory()->create();
    TenantContext::set($org);

    $domain = OrganizationDomain::query()->create([
        'domain' => 'auto.example.com',
        'type' => 'custom',
        'status' => 'pending_dns',
    ]);

    expect($domain->organization_id)->toBe($org->id);
});

// ── SlugRedirect Isolation ───────────────────────────────────────────────

it('scopes SlugRedirect queries to the current organization', function (): void {
    $orgA = Organization::factory()->create();
    $orgB = Organization::factory()->create();

    SlugRedirect::query()->withoutGlobalScopes()->create([
        'organization_id' => $orgA->id,
        'old_slug' => 'old-a',
        'redirects_to_slug' => 'new-a',
        'created_at' => now(),
    ]);

    SlugRedirect::query()->withoutGlobalScopes()->create([
        'organization_id' => $orgB->id,
        'old_slug' => 'old-b',
        'redirects_to_slug' => 'new-b',
        'created_at' => now(),
    ]);

    TenantContext::set($orgA);

    $redirects = SlugRedirect::query()->get();

    expect($redirects)->toHaveCount(1)
        ->and($redirects->first()->old_slug)->toBe('old-a');
});

it('prevents access to SlugRedirect from another organization', function (): void {
    $orgA = Organization::factory()->create();
    $orgB = Organization::factory()->create();

    $redirect = SlugRedirect::query()->withoutGlobalScopes()->create([
        'organization_id' => $orgB->id,
        'old_slug' => 'cross-slug',
        'redirects_to_slug' => 'new-cross',
        'created_at' => now(),
    ]);

    TenantContext::set($orgA);

    expect(SlugRedirect::query()->find($redirect->id))->toBeNull();
});

it('auto-assigns organization_id to SlugRedirect on create when TenantContext is set', function (): void {
    $org = Organization::factory()->create();
    TenantContext::set($org);

    $redirect = SlugRedirect::query()->create([
        'old_slug' => 'auto-old',
        'redirects_to_slug' => 'auto-new',
        'created_at' => now(),
    ]);

    expect($redirect->organization_id)->toBe($org->id);
});

// ── Cross-model isolation: switching context ─────────────────────────────

it('returns different results when switching TenantContext between organizations', function (): void {
    $orgA = Organization::factory()->create();
    $orgB = Organization::factory()->create();

    AuditLog::query()->withoutGlobalScopes()->create([
        'organization_id' => $orgA->id,
        'actor_type' => 'system',
        'action' => 'switch.a',
        'created_at' => now(),
    ]);

    AuditLog::query()->withoutGlobalScopes()->create([
        'organization_id' => $orgB->id,
        'actor_type' => 'system',
        'action' => 'switch.b',
        'created_at' => now(),
    ]);

    TenantContext::set($orgA);
    expect(AuditLog::query()->count())->toBe(1);

    TenantContext::set($orgB);
    expect(AuditLog::query()->count())->toBe(1);
    expect(AuditLog::query()->first()->action)->toBe('switch.b');
});

// ── belongsToOrganization / belongsToCurrentOrganization helpers ─────────

it('correctly identifies if AuditLog belongs to the current organization', function (): void {
    $orgA = Organization::factory()->create();
    $orgB = Organization::factory()->create();

    $log = AuditLog::query()->withoutGlobalScopes()->create([
        'organization_id' => $orgA->id,
        'actor_type' => 'system',
        'action' => 'belongs.test',
        'created_at' => now(),
    ]);

    TenantContext::set($orgA);
    expect($log->belongsToCurrentOrganization())->toBeTrue()
        ->and($log->belongsToOrganization($orgB))->toBeFalse();
});

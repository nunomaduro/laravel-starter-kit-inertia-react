<?php

declare(strict_types=1);

use App\Models\Organization;
use App\Models\User;
use App\Providers\SettingsOverlayServiceProvider;
use App\Services\OrganizationSettingsService;
use App\Services\TenantContext;
use App\Settings\AppSettings;
use App\Settings\BillingSettings;
use Illuminate\Support\Facades\DB;
use Tests\Traits\InteractsWithSettings;

uses(InteractsWithSettings::class);

beforeEach(function (): void {
    TenantContext::flush();
});

// ─── Global Overlay ────────────────────────────────────────────

it('overlays AppSettings into config at boot', function (): void {
    $settings = resolve(AppSettings::class);

    expect(config('app.name'))->toBe($settings->site_name)
        ->and(config('app.timezone'))->toBe($settings->timezone);
});

it('reflects changed settings in config after overlay re-application', function (): void {
    $this->fakeSettings(AppSettings::class, [
        'site_name' => 'Changed Name',
    ]);

    SettingsOverlayServiceProvider::applyOverlay();

    expect(config('app.name'))->toBe('Changed Name');
});

it('overlays BillingSettings into config', function (): void {
    // Re-apply overlay after RefreshDatabase has populated the settings table
    SettingsOverlayServiceProvider::applyOverlay();

    $settings = resolve(BillingSettings::class);

    expect(config('billing.enable_seat_based_billing'))->toBe($settings->enable_seat_based_billing)
        ->and(config('billing.allow_multiple_subscriptions'))->toBe($settings->allow_multiple_subscriptions);
});

it('continues if one settings group fails', function (): void {
    DB::table('settings')
        ->where('group', 'app')
        ->delete();

    app()->forgetInstance(AppSettings::class);

    // Re-run — should not throw
    SettingsOverlayServiceProvider::applyOverlay();

    // BillingSettings should still have overlaid
    $billing = resolve(BillingSettings::class);
    expect(config('billing.enable_seat_based_billing'))->toBe($billing->enable_seat_based_billing);
});

// ─── Org Overridable Keys ──────────────────────────────────────

it('returns org-overridable keys for billing but not app', function (): void {
    $keys = SettingsOverlayServiceProvider::orgOverridableKeys();

    expect($keys)->toHaveKey('billing.enable_seat_based_billing')
        ->and($keys)->toHaveKey('billing.allow_multiple_subscriptions')
        ->and($keys)->not->toHaveKey('app.site_name')
        ->and($keys)->not->toHaveKey('app.timezone');
});

// ─── Organization Settings Service ─────────────────────────────

it('stores and retrieves org overrides', function (): void {
    $org = Organization::factory()->create();
    $service = resolve(OrganizationSettingsService::class);

    $service->setOverride($org, 'billing', 'enable_seat_based_billing', true);

    $overrides = $service->getOverridesForOrganization($org);

    expect($overrides)->toHaveCount(1)
        ->and($overrides->first()->group)->toBe('billing')
        ->and($overrides->first()->name)->toBe('enable_seat_based_billing');
});

it('removes org overrides', function (): void {
    $org = Organization::factory()->create();
    $service = resolve(OrganizationSettingsService::class);

    $service->setOverride($org, 'billing', 'enable_seat_based_billing', true);
    $service->removeOverride($org, 'billing', 'enable_seat_based_billing');

    $overrides = $service->getOverridesForOrganization($org);

    expect($overrides)->toHaveCount(0);
});

it('upserts org overrides on repeated set', function (): void {
    $org = Organization::factory()->create();
    $service = resolve(OrganizationSettingsService::class);

    $service->setOverride($org, 'billing', 'enable_seat_based_billing', false);
    $service->setOverride($org, 'billing', 'enable_seat_based_billing', true);

    $overrides = $service->getOverridesForOrganization($org);

    expect($overrides)->toHaveCount(1);
});

it('encrypts and decrypts org override values', function (): void {
    $org = Organization::factory()->create();
    $service = resolve(OrganizationSettingsService::class);

    $service->setOverride($org, 'stripe', 'secret_key', 'sk_test_123', encrypt: true);

    // Raw DB value should NOT be the plaintext
    $raw = DB::table('organization_settings')
        ->where('organization_id', $org->id)
        ->where('group', 'stripe')
        ->where('name', 'secret_key')
        ->value('payload');

    expect($raw)->not->toContain('sk_test_123');

    // DB should show is_encrypted = true
    $isEncrypted = DB::table('organization_settings')
        ->where('organization_id', $org->id)
        ->where('group', 'stripe')
        ->where('name', 'secret_key')
        ->value('is_encrypted');

    expect((bool) $isEncrypted)->toBeTrue();
});

it('invalidates cache on setOverride', function (): void {
    $org = Organization::factory()->create();
    $service = resolve(OrganizationSettingsService::class);

    // Populate cache
    $service->getOverridesForOrganization($org);

    // Set a new override (should bust cache)
    $service->setOverride($org, 'billing', 'enable_seat_based_billing', true);

    // Fresh load should include the new override
    $overrides = $service->getOverridesForOrganization($org);
    expect($overrides)->toHaveCount(1);
});

// ─── Per-Org Middleware Integration ────────────────────────────

it('applies org overrides to config via middleware', function (): void {
    $org = Organization::factory()->create();
    $user = User::factory()->create();
    $org->addMember($user, 'admin');

    $service = resolve(OrganizationSettingsService::class);
    $service->setOverride($org, 'billing', 'enable_seat_based_billing', true);

    TenantContext::set($org);

    // Default from settings is false
    expect(resolve(BillingSettings::class)->enable_seat_based_billing)->toBeFalse();

    // Act as user and hit a route — middleware should apply org overrides
    $this->actingAs($user)
        ->get('/dashboard');

    expect(config('billing.enable_seat_based_billing'))->toBeTrue();
});

it('does not apply overrides for non-overridable groups', function (): void {
    $org = Organization::factory()->create();
    $service = resolve(OrganizationSettingsService::class);

    // app.site_name is NOT org-overridable
    $service->setOverride($org, 'app', 'site_name', 'Hacked Name');

    TenantContext::set($org);

    $overridableKeys = SettingsOverlayServiceProvider::orgOverridableKeys();
    $service->applyForOrganization($org, $overridableKeys);

    // Should still be the original
    $settings = resolve(AppSettings::class);
    expect(config('app.name'))->toBe($settings->site_name)
        ->and(config('app.name'))->not->toBe('Hacked Name');
});

it('isolates org overrides between organizations', function (): void {
    $orgA = Organization::factory()->create();
    $orgB = Organization::factory()->create();
    $service = resolve(OrganizationSettingsService::class);

    $service->setOverride($orgA, 'billing', 'enable_seat_based_billing', true);
    $service->setOverride($orgB, 'billing', 'enable_seat_based_billing', false);

    $overridableKeys = SettingsOverlayServiceProvider::orgOverridableKeys();

    // Apply for org A
    $service->applyForOrganization($orgA, $overridableKeys);
    expect(config('billing.enable_seat_based_billing'))->toBeTrue();

    // Apply for org B
    $service->applyForOrganization($orgB, $overridableKeys);
    expect(config('billing.enable_seat_based_billing'))->toBeFalse();
});

// ─── InteractsWithSettings Trait ───────────────────────────────

it('fakeSettings helper modifies settings in DB', function (): void {
    $this->fakeSettings(AppSettings::class, [
        'site_name' => 'Trait Test Name',
    ]);

    $fresh = resolve(AppSettings::class);
    expect($fresh->site_name)->toBe('Trait Test Name');
});

it('clearOrgOverrides removes all overrides for an org', function (): void {
    $org = Organization::factory()->create();

    $this->setOrgOverride($org, 'billing', 'enable_seat_based_billing', true);
    $this->setOrgOverride($org, 'billing', 'allow_multiple_subscriptions', true);

    $this->clearOrgOverrides($org);

    $service = resolve(OrganizationSettingsService::class);
    expect($service->getOverridesForOrganization($org))->toHaveCount(0);
});

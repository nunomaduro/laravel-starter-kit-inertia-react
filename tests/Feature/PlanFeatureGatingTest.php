<?php

declare(strict_types=1);

use App\Actions\CheckPlanFeatureAccess;
use App\Actions\CreateOrganizationAction;
use App\Models\Organization;
use App\Services\Organization\OrganizationRoleService;
use App\Services\TenantContext;
use App\Support\FeatureHelper;
use Database\Seeders\Essential\RolesAndPermissionsSeeder;
use Modules\Billing\Models\Plan;
use Spatie\Permission\PermissionRegistrar;

afterEach(function (): void {
    TenantContext::flush();
    setPermissionsTeamId(0);
});

// ---------------------------------------------------------------------------
// FeatureHelper::isActiveForKey – plan gating via TenantContext
// ---------------------------------------------------------------------------

it('FeatureHelper blocks plan-gated feature when org has no subscription', function (): void {
    $org = Organization::factory()->create();
    TenantContext::set($org);

    // 'api_access' has plan_required: 'pro' in config/feature-flags.php
    $result = FeatureHelper::isActiveForKey('api_access');

    expect($result)->toBeFalse();
});

it('FeatureHelper allows plan-gated feature when org has the required plan', function (): void {
    $plan = Plan::factory()->create(['slug' => 'pro']);
    $org = Organization::factory()->create();
    $org->newPlanSubscription('main', $plan);

    TenantContext::set($org);

    // 'pro' plan includes 'api_access' per config/billing.php plan_features
    $result = FeatureHelper::isActiveForKey('api_access');

    expect($result)->toBeTrue();
});

// ---------------------------------------------------------------------------
// CheckPlanFeatureAccess action – direct unit-style coverage
// ---------------------------------------------------------------------------

it('CheckPlanFeatureAccess blocks feature when org has no plan', function (): void {
    $org = Organization::factory()->create();

    $result = (new CheckPlanFeatureAccess)->handle($org, 'api_access');

    expect($result)->toBeFalse();
});

it('CheckPlanFeatureAccess allows feature when org has the correct plan', function (): void {
    $plan = Plan::factory()->create(['slug' => 'pro']);
    $org = Organization::factory()->create();
    $org->newPlanSubscription('main', $plan);

    $result = (new CheckPlanFeatureAccess)->handle($org, 'api_access');

    expect($result)->toBeTrue();
});

// ---------------------------------------------------------------------------
// OrgFeaturesController – features settings page includes plan props
// ---------------------------------------------------------------------------

it('features settings page includes orgPlan and planFeatures props', function (): void {
    $this->seed(RolesAndPermissionsSeeder::class);

    $user = createTestUser();
    $organization = resolve(CreateOrganizationAction::class)->handle($user, 'Test Org');
    resolve(OrganizationRoleService::class)->syncRolePermissions($organization);
    resolve(PermissionRegistrar::class)->forgetCachedPermissions();

    $plan = Plan::factory()->create(['slug' => 'pro']);
    $organization->newPlanSubscription('main', $plan);

    TenantContext::set($organization);
    setPermissionsTeamId(null);

    $this->withoutMiddleware(App\Http\Middleware\HandleInertiaRequests::class);

    $this->actingAs($user)
        ->get(route('settings.features.show'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('settings/features')
            ->has('orgPlan')
            ->has('planFeatures')
            ->where('orgPlan', 'pro')
        );
});

it('features settings page returns null orgPlan when org has no subscription', function (): void {
    $this->seed(RolesAndPermissionsSeeder::class);

    $user = createTestUser();
    $organization = resolve(CreateOrganizationAction::class)->handle($user, 'Test Org');
    resolve(OrganizationRoleService::class)->syncRolePermissions($organization);
    resolve(PermissionRegistrar::class)->forgetCachedPermissions();

    TenantContext::set($organization);
    setPermissionsTeamId(null);

    $this->withoutMiddleware(App\Http\Middleware\HandleInertiaRequests::class);

    $this->actingAs($user)
        ->get(route('settings.features.show'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('settings/features')
            ->has('orgPlan')
            ->has('planFeatures')
            ->where('orgPlan', null)
        );
});

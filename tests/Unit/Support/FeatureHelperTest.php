<?php

declare(strict_types=1);

use App\Models\Organization;
use App\Models\User;
use App\Services\OrganizationSettingsService;
use App\Services\TenantContext;
use App\Support\FeatureHelper;
use Laravel\Pennant\Feature;
use Modules\Billing\Models\Plan;
use Modules\Blog\Features\BlogFeature;

test('isActiveForKey returns false when feature is globally disabled', function (): void {
    config(['feature-flags.globally_disabled' => ['blog']]);

    $user = User::factory()->withoutTwoFactor()->create([
        'onboarding_completed' => true,
    ]);
    Feature::for($user)->activate(BlogFeature::class);

    expect(FeatureHelper::isActiveForKey('blog', $user))->toBeFalse();
});

test('isActiveForClass returns false when feature is globally disabled', function (): void {
    config(['feature-flags.globally_disabled' => ['blog']]);

    $user = User::factory()->withoutTwoFactor()->create([
        'onboarding_completed' => true,
    ]);
    Feature::for($user)->activate(BlogFeature::class);

    expect(FeatureHelper::isActiveForClass(BlogFeature::class, $user))->toBeFalse();
});

test('isActiveForKey returns false when org plan does not include plan-gated feature', function (): void {
    $user = createTestUser();
    $org = Organization::factory()->create();
    TenantContext::set($org);

    // api_access has plan_required = 'pro', basic plan doesn't include it
    $plan = Plan::create([
        'slug' => 'basic',
        'name' => ['en' => 'Basic'],
        'description' => ['en' => 'Basic plan'],
        'is_active' => true,
        'price' => 0,
        'currency' => 'usd',
        'invoice_period' => 1,
        'invoice_interval' => 'month',
        'grace_period' => 0,
        'grace_interval' => 'day',
        'trial_period' => 0,
        'trial_interval' => 'day',
    ]);
    $org->newPlanSubscription('main', $plan);

    config(['billing.plan_features.basic' => []]);

    expect(FeatureHelper::isActiveForKey('api_access', $user))->toBeFalse();
});

test('org override cannot bypass plan restriction', function (): void {
    $user = createTestUser();
    $org = Organization::factory()->create();
    TenantContext::set($org);

    // No subscription — api_access requires 'pro'
    // Org admin sets override to 'enabled'
    resolve(OrganizationSettingsService::class)->setOverride($org, 'features', 'api_access', 'enabled');

    // Should still be false — plan restriction trumps org override
    expect(FeatureHelper::isActiveForKey('api_access', $user))->toBeFalse();
});

test('isGloballyDisabled returns true for disabled feature key', function (): void {
    config(['feature-flags.globally_disabled' => ['blog', 'changelog']]);

    expect(FeatureHelper::isGloballyDisabled('blog'))->toBeTrue()
        ->and(FeatureHelper::isGloballyDisabled('changelog'))->toBeTrue()
        ->and(FeatureHelper::isGloballyDisabled('help'))->toBeFalse();
});

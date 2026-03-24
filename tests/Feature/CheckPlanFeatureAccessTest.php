<?php

declare(strict_types=1);

use App\Actions\CheckPlanFeatureAccess;
use App\Models\Organization;
use Modules\Billing\Models\Plan;

it('returns true when the feature has no plan_required', function (): void {
    // 'onboarding' has plan_required: null in config/feature-flags.php
    $org = Organization::factory()->create();

    $result = (new CheckPlanFeatureAccess)->handle($org, 'onboarding');

    expect($result)->toBeTrue();
});

it('returns false when org has no subscription and feature requires a plan', function (): void {
    // 'api_access' has plan_required: 'pro' in config/feature-flags.php
    $org = Organization::factory()->create();

    $result = (new CheckPlanFeatureAccess)->handle($org, 'api_access');

    expect($result)->toBeFalse();
});

it('returns true when the org plan includes the feature', function (): void {
    $plan = Plan::factory()->create(['slug' => 'pro']);
    $org = Organization::factory()->create();
    $org->newPlanSubscription('main', $plan);

    // 'pro' plan includes 'api_access' in config/billing.php plan_features
    $result = (new CheckPlanFeatureAccess)->handle($org, 'api_access');

    expect($result)->toBeTrue();
});

it('returns false when the org plan does not include the feature', function (): void {
    $plan = Plan::factory()->create(['slug' => 'basic']);
    $org = Organization::factory()->create();
    $org->newPlanSubscription('main', $plan);

    // 'basic' plan has no features in config/billing.php plan_features
    $result = (new CheckPlanFeatureAccess)->handle($org, 'api_access');

    expect($result)->toBeFalse();
});

it('returns true for a feature unknown to registry (no metadata means no plan required)', function (): void {
    $org = Organization::factory()->create();

    // A feature key not in the registry should default to no plan_required (returns null)
    $result = (new CheckPlanFeatureAccess)->handle($org, 'nonexistent_feature_xyz');

    expect($result)->toBeTrue();
});

<?php

declare(strict_types=1);

use App\Features\BlogFeature;
use App\Models\User;
use App\Support\FeatureHelper;
use Laravel\Pennant\Feature;

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

test('isGloballyDisabled returns true for disabled feature key', function (): void {
    config(['feature-flags.globally_disabled' => ['blog', 'changelog']]);

    expect(FeatureHelper::isGloballyDisabled('blog'))->toBeTrue()
        ->and(FeatureHelper::isGloballyDisabled('changelog'))->toBeTrue()
        ->and(FeatureHelper::isGloballyDisabled('help'))->toBeFalse();
});

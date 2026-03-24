<?php

declare(strict_types=1);

use App\Actions\CompleteOnboardingAction;
use App\Models\User;

it('marks the user onboarding as completed', function (): void {
    $user = User::factory()->withoutTwoFactor()->create(['onboarding_completed' => false]);

    $action = resolve(CompleteOnboardingAction::class);
    $action->handle($user);

    expect($user->fresh()->onboarding_completed)->toBeTrue();
});

it('is idempotent when called multiple times', function (): void {
    $user = User::factory()->withoutTwoFactor()->create(['onboarding_completed' => true]);

    $action = resolve(CompleteOnboardingAction::class);
    $action->handle($user);

    expect($user->fresh()->onboarding_completed)->toBeTrue();
});

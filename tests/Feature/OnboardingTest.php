<?php

declare(strict_types=1);

use App\Features\OnboardingFeature;
use App\Models\User;
use App\Settings\SetupWizardSettings;
use Database\Seeders\Essential\RolesAndPermissionsSeeder;
use Laravel\Pennant\Feature;

beforeEach(function (): void {
    $settings = resolve(SetupWizardSettings::class);
    $settings->setup_completed = true;
    $settings->save();
});

test('users without completed onboarding are redirected to next unfinished step', function (): void {
    $this->seed(RolesAndPermissionsSeeder::class);
    $user = User::withoutEvents(fn (): User => User::factory()->withoutTwoFactor()->needsOnboarding()->create());
    assignRoleForTestUser($user, 'user');

    $nextStep = $user->onboarding()->nextUnfinishedStep();
    expect($nextStep)->not->toBeNull();

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertRedirect($nextStep->link);
});

test('users with completed onboarding can access dashboard', function (): void {
    $this->seed(RolesAndPermissionsSeeder::class);
    $user = User::withoutEvents(fn (): User => User::factory()->withoutTwoFactor()->create([
        'onboarding_completed' => true,
    ]));
    assignRoleForTestUser($user, 'user');

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertOk();
});

test('onboarding page is accessible for incomplete users', function (): void {
    $this->seed(RolesAndPermissionsSeeder::class);
    $user = User::withoutEvents(fn (): User => User::factory()->withoutTwoFactor()->needsOnboarding()->create());
    assignRoleForTestUser($user, 'user');

    $this->actingAs($user)
        ->get(route('onboarding'))
        ->assertOk();
});

test('completed users can view onboarding page again for review', function (): void {
    $this->seed(RolesAndPermissionsSeeder::class);
    $user = User::withoutEvents(fn (): User => User::factory()->withoutTwoFactor()->create([
        'onboarding_completed' => true,
    ]));
    assignRoleForTestUser($user, 'user');

    $response = $this->actingAs($user)
        ->get(route('onboarding'))
        ->assertOk();

    $response->assertInertia(fn ($page) => $page
        ->component('onboarding/show')
        ->where('alreadyCompleted', true));
});

test('can complete onboarding', function (): void {
    $this->seed(RolesAndPermissionsSeeder::class);
    $user = User::withoutEvents(fn (): User => User::factory()->withoutTwoFactor()->needsOnboarding()->create());
    assignRoleForTestUser($user, 'user');

    $this->actingAs($user)
        ->post(route('onboarding.store'))
        ->assertRedirect(route('dashboard'));

    $user->refresh();
    expect($user->onboarding_completed)->toBeTrue();
});

test('logout is accessible without completing onboarding', function (): void {
    $this->seed(RolesAndPermissionsSeeder::class);
    $user = User::withoutEvents(fn (): User => User::factory()->withoutTwoFactor()->needsOnboarding()->create());
    assignRoleForTestUser($user, 'user');

    $this->actingAs($user)
        ->post(route('logout'))
        ->assertRedirect();
});

test('when onboarding feature is inactive user can access dashboard without completing onboarding', function (): void {
    $this->seed(RolesAndPermissionsSeeder::class);
    $user = User::withoutEvents(fn (): User => User::factory()->withoutTwoFactor()->needsOnboarding()->create());
    assignRoleForTestUser($user, 'user');
    Feature::for($user)->deactivate(OnboardingFeature::class);

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertOk();
});

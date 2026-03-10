<?php

declare(strict_types=1);

use App\Models\User;
use App\Settings\SetupWizardSettings;

test('authenticated user with completed onboarding can access dashboard', function (): void {
    $user = User::factory()->withoutTwoFactor()->create([
        'onboarding_completed' => true,
    ]);

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('dashboard')
            ->has('auth')
            ->where('auth.user.id', $user->id)
            ->has('auth.permissions')
            ->has('auth.roles')
        );
});

test('dashboard response includes setup_complete from shared props when setup is complete', function (): void {
    $settings = resolve(SetupWizardSettings::class);
    $settings->setup_completed = true;
    $settings->save();

    $user = User::factory()->withoutTwoFactor()->create(['onboarding_completed' => true]);

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('dashboard')
            ->where('setup_complete', true)
        );
});

test('dashboard response includes setup_complete false when setup is incomplete', function (): void {
    $settings = resolve(SetupWizardSettings::class);
    $settings->setup_completed = false;
    $settings->save();

    $user = User::factory()->withoutTwoFactor()->create(['onboarding_completed' => true]);

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('dashboard')
            ->where('setup_complete', false)
        );
});

test('dashboard requires authentication', function (): void {
    $this->get(route('dashboard'))
        ->assertRedirect();
});

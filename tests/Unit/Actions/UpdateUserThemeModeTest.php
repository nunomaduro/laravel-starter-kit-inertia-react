<?php

declare(strict_types=1);

use App\Actions\UpdateUserThemeMode;
use App\Models\User;

it('updates the user theme mode to dark', function (): void {
    $user = User::factory()->withoutTwoFactor()->create(['theme_mode' => 'light']);

    $action = resolve(UpdateUserThemeMode::class);
    $action->handle($user, 'dark');

    expect($user->fresh()->theme_mode)->toBe('dark');
});

it('updates the user theme mode to light', function (): void {
    $user = User::factory()->withoutTwoFactor()->create(['theme_mode' => 'dark']);

    $action = resolve(UpdateUserThemeMode::class);
    $action->handle($user, 'light');

    expect($user->fresh()->theme_mode)->toBe('light');
});

it('updates the user theme mode to system', function (): void {
    $user = User::factory()->withoutTwoFactor()->create(['theme_mode' => 'dark']);

    $action = resolve(UpdateUserThemeMode::class);
    $action->handle($user, 'system');

    expect($user->fresh()->theme_mode)->toBe('system');
});

<?php

declare(strict_types=1);

use App\Actions\BatchUpdateUsersAction;
use App\Models\User;

it('updates allowed column for multiple users', function (): void {
    $users = User::factory()->withoutTwoFactor()->count(3)->create(['name' => 'Original']);

    $action = resolve(BatchUpdateUsersAction::class);
    $count = $action->handle($users->pluck('id')->all(), 'name', 'Updated');

    expect($count)->toBe(3);

    foreach ($users as $user) {
        expect($user->fresh()->name)->toBe('Updated');
    }
});

it('rejects disallowed columns and returns zero', function (): void {
    $user = User::factory()->withoutTwoFactor()->create();

    $action = resolve(BatchUpdateUsersAction::class);
    $count = $action->handle([$user->id], 'email', 'hacked@example.com');

    expect($count)->toBe(0)
        ->and($user->fresh()->email)->not->toBe('hacked@example.com');
});

it('updates onboarding_completed as boolean', function (): void {
    $users = User::factory()->withoutTwoFactor()->count(2)->create(['onboarding_completed' => false]);

    $action = resolve(BatchUpdateUsersAction::class);
    $count = $action->handle($users->pluck('id')->all(), 'onboarding_completed', true);

    expect($count)->toBe(2);

    foreach ($users as $user) {
        expect((bool) $user->fresh()->onboarding_completed)->toBeTrue();
    }
});

it('handles empty ids array gracefully', function (): void {
    $action = resolve(BatchUpdateUsersAction::class);
    $count = $action->handle([], 'name', 'Nothing');

    expect($count)->toBe(0);
});

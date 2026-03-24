<?php

declare(strict_types=1);

use App\Actions\BulkSoftDeleteUsers;
use App\Models\User;

it('soft-deletes the specified users', function (): void {
    $users = User::factory()->withoutTwoFactor()->count(3)->create();

    $action = resolve(BulkSoftDeleteUsers::class);
    $count = $action->handle($users->pluck('id')->all(), null);

    expect($count)->toBe(3);

    foreach ($users as $user) {
        expect($user->fresh()->trashed())->toBeTrue();
    }
});

it('skips the current user when bulk deleting', function (): void {
    $currentUser = User::factory()->withoutTwoFactor()->create();
    $otherUser = User::factory()->withoutTwoFactor()->create();

    $action = resolve(BulkSoftDeleteUsers::class);
    $count = $action->handle([$currentUser->id, $otherUser->id], $currentUser);

    expect($count)->toBe(1)
        ->and($currentUser->fresh()->trashed())->toBeFalse()
        ->and($otherUser->fresh()->trashed())->toBeTrue();
});

it('handles empty ids array gracefully', function (): void {
    $action = resolve(BulkSoftDeleteUsers::class);
    $count = $action->handle([], null);

    expect($count)->toBe(0);
});

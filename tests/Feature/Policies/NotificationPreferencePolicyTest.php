<?php

declare(strict_types=1);

use App\Models\NotificationPreference;
use App\Models\User;

it('allows any authenticated user to view any notification preferences', function (): void {
    $user = User::factory()->withoutTwoFactor()->create();

    expect($user->can('viewAny', NotificationPreference::class))->toBeTrue();
});

it('allows owner to view their notification preference', function (): void {
    $user = User::factory()->withoutTwoFactor()->create();
    $preference = NotificationPreference::factory()->create(['user_id' => $user->id]);

    expect($user->can('view', $preference))->toBeTrue();
});

it('denies non-owner from viewing another users notification preference', function (): void {
    $owner = User::factory()->withoutTwoFactor()->create();
    $other = User::factory()->withoutTwoFactor()->create();
    $preference = NotificationPreference::factory()->create(['user_id' => $owner->id]);

    expect($other->can('view', $preference))->toBeFalse();
});

it('allows any authenticated user to create a notification preference', function (): void {
    $user = User::factory()->withoutTwoFactor()->create();

    expect($user->can('create', NotificationPreference::class))->toBeTrue();
});

it('allows owner to update their notification preference', function (): void {
    $user = User::factory()->withoutTwoFactor()->create();
    $preference = NotificationPreference::factory()->create(['user_id' => $user->id]);

    expect($user->can('update', $preference))->toBeTrue();
});

it('denies non-owner from updating another users notification preference', function (): void {
    $owner = User::factory()->withoutTwoFactor()->create();
    $other = User::factory()->withoutTwoFactor()->create();
    $preference = NotificationPreference::factory()->create(['user_id' => $owner->id]);

    expect($other->can('update', $preference))->toBeFalse();
});

it('allows owner to delete their notification preference', function (): void {
    $user = User::factory()->withoutTwoFactor()->create();
    $preference = NotificationPreference::factory()->create(['user_id' => $user->id]);

    expect($user->can('delete', $preference))->toBeTrue();
});

it('denies non-owner from deleting another users notification preference', function (): void {
    $owner = User::factory()->withoutTwoFactor()->create();
    $other = User::factory()->withoutTwoFactor()->create();
    $preference = NotificationPreference::factory()->create(['user_id' => $owner->id]);

    expect($other->can('delete', $preference))->toBeFalse();
});

it('allows owner to restore their notification preference', function (): void {
    $user = User::factory()->withoutTwoFactor()->create();
    $preference = NotificationPreference::factory()->create(['user_id' => $user->id]);

    expect($user->can('restore', $preference))->toBeTrue();
});

it('denies non-owner from restoring another users notification preference', function (): void {
    $owner = User::factory()->withoutTwoFactor()->create();
    $other = User::factory()->withoutTwoFactor()->create();
    $preference = NotificationPreference::factory()->create(['user_id' => $owner->id]);

    expect($other->can('restore', $preference))->toBeFalse();
});

it('allows owner to force delete their notification preference', function (): void {
    $user = User::factory()->withoutTwoFactor()->create();
    $preference = NotificationPreference::factory()->create(['user_id' => $user->id]);

    expect($user->can('forceDelete', $preference))->toBeTrue();
});

it('denies non-owner from force deleting another users notification preference', function (): void {
    $owner = User::factory()->withoutTwoFactor()->create();
    $other = User::factory()->withoutTwoFactor()->create();
    $preference = NotificationPreference::factory()->create(['user_id' => $owner->id]);

    expect($other->can('forceDelete', $preference))->toBeFalse();
});
